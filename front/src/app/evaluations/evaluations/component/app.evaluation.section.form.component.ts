import { AfterViewInit, Component, EventEmitter, Input, OnDestroy, OnInit, Output } from '@angular/core';
import { FormGroup, FormControl, Validators } from '@angular/forms';
import { IMultiSelectSettings, IMultiSelectTexts } from 'angular-2-dropdown-multiselect';

// Classes
import { Evaluation, Section, SectionType } from '../../class/app.evaluation.class';
import { FileType } from '../../../core/class/app.file.class';

// Animations
import { slideInOutAnimation } from '../../../../animations/slide.animations';

// Services
import { AppCoreRestService } from '../../../core/service/app.core.rest.service';
import { AppCoreFormService } from '../../../core/service/app.core.form.service';
import { AppAuthAuthenticationService } from '../../../auth/service/app.auth.authentication.service';
import { AppCoreLoaderService } from '../../../core/service/app.core.loader.service';

// Validators
import { LenMinValidator } from '../../../core/validator/app.core.validator';
import { StepValidator } from '../../../core/validator/app.core.step.validator';

// -----

@Component ({
  selector: 'app-evaluation-section-form',
  templateUrl: '../html/app.evaluation.section.form.component.html',
  animations: [slideInOutAnimation],
  host: { '[@slideInOutAnimation]': '' }
})
export class AppEvaluationSectionFormComponent implements OnInit, OnDestroy, AfterViewInit {

  @Input() evaluation: Evaluation;
  @Input() section: Section;
  @Input() getViewSectionForm: Boolean;
  @Input() sectionsControl: FormControl;
  @Output() evaluationChange = new EventEmitter();
  @Output() getViewSectionFormChange = new EventEmitter();
  @Output() sectionsControlChange = new EventEmitter();
  maxSizeSetting: number;
  // FORM
  invalidForm: boolean;
  sectionForm: FormGroup;
  sectionGroup: FormGroup;
  sectionTitleControl: FormControl;
  sectionSubjectControl: FormControl;
  sectionTypeControl: FormControl;
  sectionFileGroup: FormGroup;
  sectionFileLimitFileTypesControl: FormControl;
  sectionFileTypesControl: FormControl;
  sectionFileMaxSizeControl: FormControl;
  // SELECT
  sectionTypes: {}[] = [];
  sectionTypesObjects: SectionType[] = [];
  singleSelectSettings: IMultiSelectSettings;
  sectionTypesSelectTexts: IMultiSelectTexts;
  fileTypes: {}[] = [];
  fileTypesObjects: FileType[] = [];
  multipleSelectSettings: IMultiSelectSettings;
  fileTypesSelectTexts: IMultiSelectTexts;
  // WYSIWYG
  editors = {};
  // ERRORS
  backChecks = null;
  saveChecks = null;
  backControls = {};
  saveControls = {};

  constructor(
    private restService: AppCoreRestService,
    private authService: AppAuthAuthenticationService,
    private loaderService: AppCoreLoaderService,
    private formService: AppCoreFormService,
  ) {
    console.log('__CONSTRUCT__ app.evaluation.section.form.component');
    this.authService.checkRole(['teacher'], true);
  }

  ngOnInit(): void {
    this.invalidForm = false;
    console.log(this.evaluation);
    console.log(this.section);
    // SELECT
    this.singleSelectSettings = this.formService.singleSelectSettings();
    this.multipleSelectSettings = this.formService.multipleSelectSettings();
    this.getSectionTypes();
    this.getFileTypes();
    this.getMaxSizeSetting();
    // CHECKS
    this.checksFront(true);
    this.checksBack(true);
  }

  // WYSIWYG
  ngAfterViewInit() {
    if (this.evaluation && this.sectionForm && Object.keys(this.editors).length === 0) {
      this.formService.wysiwyg(this.editors, 'sectionSubject', this.sectionSubjectControl, this.evaluation.active_assignment);
    }
  }
  ngAfterViewChecked() {
    this.ngAfterViewInit();
  }
  ngOnDestroy() {
    this.editors = this.formService.wysiwyg_remove(this.editors);
  }
  //CHECKS
  checks() {
    this.sectionForm.valueChanges.subscribe(data => { this.checksFront(); });
  }
  checksFront(init: boolean = false) {
    // Function
    if (init) {
      this.saveControls = {
        'sectionTitle':     [this.sectionTitleControl,    {
          'required' : 'Le titre de la section est requis.'
        }, 'Titre', null],
        'sectionSubject':     [this.sectionSubjectControl,    {
          'required' : 'Le sujet de la section est requise.'
        }, 'Sujet', null],
        'sectionType':     [this.sectionTypeControl,    {
          'required' : 'Le type de section est requis.',
          'maxNumber:1': 'Vous ne pouvez sélectionner qu\'un seul type.'
        }, 'Type', null],
        'sectionFileLimitFileTypes':     [this.sectionFileLimitFileTypesControl,    {
          'required' : 'Le fait de savoir si les types sont limités ou non est requis.'
        }, 'Types de fichier limités', 'sectionType:2'],
        'sectionFileTypes':     [this.sectionFileTypesControl,    {
          'required' : 'Les types de fichiers sont requis.',
          'minNumber:1': 'Vous devez sélectionner au moins un type.'
        }, 'Types de fichier', 'sectionType:2&&sectionFileLimitFileTypes:true'],
        'maxSize' : [this.maxSizeSetting, {}, 'Taille maximale des fichiers', null],
        'sectionFileMaxSize':     [this.sectionFileMaxSizeControl,    {
          'min:0.000000000001' : 'La taille minimale du fichier doit être supérieure à 0.',
          'max:maxSize' : 'La taille maximale du fichier ne dois pas dépasser ' + this.maxSizeSetting + 'Mo.'
        }, 'Taille du fichier', 'sectionType:2'],
      };
    }
    this.saveChecks = this.formService.checkFrontErrors(this.saveControls);
  }
  checksBack(init: boolean = false, response = []) {
    if (init) {
      this.backControls = [
        'sectionTitle',
        'sectionSubject',
        'sectionType',
        'sectionFileLimitFileTypes',
        'sectionFileTypes',
        'sectionFileMaxSize'
      ];
    }
    this.backChecks = this.formService.checkBackErrors(this.backControls, response);
  }

  // SELECT
  getSectionTypes(): void {
    this.loaderService.display(true);
    this.sectionTypesSelectTexts = this.formService.selectTexts(' le type de section...');
    this.restService.getDb('sectionTypes')
      .then(sectionTypes => {
        sectionTypes.forEach(sectionType => {
          this.sectionTypes.push({id: sectionType.id, name: sectionType.label});
          this.sectionTypesObjects.push(new SectionType(sectionType));
        });
        this.loaderService.display(false);
      });
  }
  getFileTypes(): void {
    this.loaderService.display(true);
    this.fileTypesSelectTexts = this.formService.selectTexts(' les types de fichier...');
    this.restService.getDb('fileTypes')
      .then(fileTypes => {
        fileTypes.forEach(fileType => {
          this.fileTypes.push({id: fileType.id, name: fileType.type});
          this.fileTypesObjects.push(new FileType(fileType));
        });
        this.loaderService.display(false);
      });
  }
  getMaxSizeSetting(): void {
    this.loaderService.display(true);
    this.restService.getDb('maxSizeSetting')
      .then(maxSizeSetting => {
        this.maxSizeSetting = parseInt(maxSizeSetting.value, 10);
      })
      .then(() => {
        this.setForm();
        // CHECKS
        this.checks();
        this.checksFront(true);
        this.checksBack(true);
        this.loaderService.display(false);
      });
  }

  // FORM
  setForm(): void {
    this.sectionForm = new FormGroup({
      section: this.sectionGroup = new FormGroup({
        sectionTitle: this.sectionTitleControl = new FormControl(
          this.section.title,
          // Validators.required
        ),
        sectionSubject: this.sectionSubjectControl = new FormControl(
          this.section.subject,
          // Validators.required
        ),
        sectionType: this.sectionTypeControl = new FormControl(
          this.section.getSectionTypeId(),
          // Validators.required
        )
      }),
      sectionFile: this.sectionFileGroup = new FormGroup({
        sectionFileLimitFileTypes: this.sectionFileLimitFileTypesControl = new FormControl(
          this.section.limit_file_types,
          // Validators.required
        ),
        sectionFileTypes: this.sectionFileTypesControl = new FormControl(
          this.section.getSectionFileTypesIds(),
          // LenMinValidator(1)
        ),
        sectionFileMaxSize: this.sectionFileMaxSizeControl = new FormControl(
          this.section.max_size,
          /* Validators.compose([
            Validators.min(0),
            Validators.max(this.maxSizeSetting),
            StepValidator(1)
          ])*/
        )
      })
    });
  }

  // SECTION
  cancel(): void {
    this.getViewSectionForm = false;
    this.getViewSectionFormChange.emit(false);
    // CHECKS
    this.sectionForm.markAsPristine();
    this.sectionForm.markAsUntouched();
    this.checksFront();
  }
  isFormValid(): boolean {
    return ((this.sectionGroup.valid && this.sectionTypeControl.value != 2)
      || (this.sectionGroup.valid && this.sectionFileLimitFileTypesControl.valid && this.sectionFileMaxSizeControl.valid
        && (!this.sectionFileLimitFileTypesControl.value
          || (this.sectionFileLimitFileTypesControl.value && this.sectionFileTypesControl.value.length > 0))));
  }
  saveSection(): void {
    if (this.formService.checkEmptyChecks(this.saveChecks)) {
      this.applyFormData();
      // UPDATE
      if (this.section.id || this.section.id === 0) {
        const index = this.evaluation.sections.findIndex(section => section.id === this.section.id);
        this.evaluation.sections[index] = this.section;
        // NEW
      } else {
        this.evaluation.sections.push(this.section);
      }
      if (this.section.id === 0) {
        this.section.id = null;
      }
      this.evaluationChange.emit(this.evaluation);
      // SectionsControl
      const sectionsId = [];
      this.evaluation.sections.forEach(section => {
        sectionsId.push(section.id);
      });
      this.sectionsControl.setValue(sectionsId);
      this.sectionsControlChange.emit(this.sectionsControl);
      this.cancel();
    }
  }

  //
  applyFormData() {
    this.section.title = this.sectionTitleControl.value;
    this.section.subject = this.sectionSubjectControl.value;
    this.section.section_type = this.sectionTypesObjects.find(type => type.id === this.sectionTypeControl.value[0]);
    this.section.order = !this.section.order ? this.evaluation.sections.length + 1 : this.section.order;
    if (this.sectionTypeControl.value == 2) {
      this.section.max_size = ( this.sectionFileMaxSizeControl.value ? this.sectionFileMaxSizeControl.value : this.maxSizeSetting );
      this.section.limit_file_types = this.sectionFileLimitFileTypesControl.value;
      if (this.sectionFileLimitFileTypesControl.value) {
        this.section.file_types = [];
        this.sectionFileTypesControl.value.forEach(sectionFileTypeControlValue => {
          this.section.file_types.push(this.fileTypesObjects.find(type => type.id == sectionFileTypeControlValue));
        });
      }
    }
  }

}

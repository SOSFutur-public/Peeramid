import { AfterViewChecked, AfterViewInit, Component, Input, Output, OnDestroy, OnInit, EventEmitter, ViewChild, ElementRef } from '@angular/core';
import { FormGroup, FormControl } from '@angular/forms';
import { Router } from '@angular/router';
import { IMultiSelectSettings, IMultiSelectTexts } from 'angular-2-dropdown-multiselect';
import { FileUploader} from 'ng2-file-upload';
import { BsDatepickerConfig } from 'ngx-bootstrap/datepicker';
import { TimepickerConfig } from 'ngx-bootstrap';
import { isUndefined } from 'ngx-bootstrap/bs-moment/utils/type-checks';

// Environment
import { environment } from '../../../../environments/environment';

// Classes
import { Evaluation, Section, SectionType } from '../../class/app.evaluation.class';

// Animations
import { routerAnimation } from '../../../../animations/router.animations';

// Functions
import { joinDateAndTime } from '../../../core/functions/app.core.utils.functions';

// Services
import { AppCoreRestService } from '../../../core/service/app.core.rest.service';
import { AppCoreFilterService } from '../../../core/service/app.core.filter.service';
import { AppCoreFormService } from '../../../core/service/app.core.form.service';
import { AppCoreAlertService } from '../../../core/service/app.core.alert.service';
import { AppAuthAuthenticationService } from '../../../auth/service/app.auth.authentication.service';
import { AppCoreLoaderService } from '../../../core/service/app.core.loader.service';
import { AppEvaluationService } from '../service/app.evaluation.service';

// Exports
export function getTimepickerConfig(): TimepickerConfig {
  return Object.assign(new TimepickerConfig(), {
    hourStep: 1,
    minuteStep: 1,
    showMeridian: false,
    readonlyInput: false,
    mousewheel: false,
  });
}

// -----

@Component ({
  selector: 'app-evaluation-form',
  templateUrl: '../html/app.evaluation.form.component.html',
  animations: [routerAnimation],
  host: { '[@routerAnimation]': '' },
  providers: [{provide: TimepickerConfig, useFactory: getTimepickerConfig}]
})
export class AppEvaluationFormComponent implements OnInit, OnDestroy, AfterViewInit, AfterViewChecked {

  @Input() evaluation: Evaluation;
  @Input() dirty: boolean;
  @Output() dirtyChange = new EventEmitter<boolean>();
  _evaluation: any;
  activeCorrection_tmp: boolean;
  // ENV
  environment = environment;
  // FORM
  evaluationForm: FormGroup;
  invalidForm = false;
  // DIVERS
  sectionTypes: SectionType[];
  sectionUpdateActive: boolean;
  sectionOrderTmp: number;
  // CONTROLS
  nameControl: FormControl;
  lessonControl: FormControl;
  subjectControl: FormControl;
  datesAssignmentGroup: FormGroup;
  dateStartAssignmentControl: FormControl;
  dateEndAssignmentControl: FormControl;
  timeStartAssignmentControl: FormControl;
  timeEndAssignmentControl: FormControl;
  individualAssignmentControl: FormControl;
  assignmentInstructionsControl: FormControl;
  exampleAssignmentsControl: FormControl;
  usersControl: FormControl;
  groupsControl: FormControl;
  sectionsControl: FormControl;
  // SELECT
  lessons: {}[] = [];
  users: {}[] = [];
  groups: {}[] = [];
  singleSelectSettings: IMultiSelectSettings;
  multipleSelectSettings: IMultiSelectSettings;
  lessonsSelectTexts: IMultiSelectTexts;
  usersSelectTexts: IMultiSelectTexts;
  groupsSelectTexts: IMultiSelectTexts;
  // DATETIME
  currentDate: Date = new Date();
  minDate: Date = new Date();
  locale = 'fr';
  _bsValue: Date;
  bsConfig: Partial<BsDatepickerConfig>;
  // FILES
  @ViewChild('uploaderSubjectElem') uploaderSubjectElemRef: ElementRef;
  @ViewChild('uploaderExamplesElem') uploaderExamplesElemRef: ElementRef;
  uploaderSubject: FileUploader;
  uploaderExamples: FileUploader;
  uploadErrors: string[][] = [];
  filesMaxSizeSetting: number;
  filesTypesSetting = [];
  // WYSIWYG
  editors = {};
  // VIEWS
  view_section_form: Boolean = false;
  section_tmp: Section;
  sectionUpdated: boolean;
  // ERRORS
  backChecks = null;
  saveChecks = null;
  activateChecks = null;
  backControls = {};
  saveControls = {};
  activateControls = {};

  constructor(
    private router: Router,
    private restService: AppCoreRestService,
    private filterService: AppCoreFilterService,
    private authService: AppAuthAuthenticationService,
    private loaderService: AppCoreLoaderService,
    private alertService: AppCoreAlertService,
    private formService: AppCoreFormService,
    private evaluationService: AppEvaluationService
  ) {
    console.log('__CONSTRUCT__ app.evaluation.form.component');
    this.authService.checkRole(['teacher'], true);
    // Current Date
    setInterval(() => {
      this.currentDate =  new Date();
      this.dirtyChange.emit(this.evaluationForm.dirty);
    }, 1000);
  }

  canDeactivate(): boolean {
    if (this.evaluationForm && this.evaluationForm.dirty) {
      return confirm('Êtes-vous sûr de quitter le formulaire sans enregistrer vos modifications?');
    }
    return true;
  }

  ngOnInit(): void {
    // SORT
    this.filterService.sortList(this.evaluation.sections, 'order', true);
    // SELECT
    this.singleSelectSettings = this.formService.singleSelectSettings();
    this.multipleSelectSettings = this.formService.multipleSelectSettings();
    this.getLessons();
    this.getUsers();
    // DATETIME PICKER
    this.bsConfig = Object.assign({}, {
      locale: this.locale,
      showWeekNumbers: true,
    });
    // FILES
    this.getMaxSizeSetting();
    this.uploaderSubject = new FileUploader({
      headers: [{name: 'Authorization' , value: 'Bearer ' + this.authService.getToken()}],
      url: environment.api_url + 'evaluations/' + this.evaluation.id + '/subjects',
      removeAfterUpload: true
    });
    this.uploaderSubject.onAfterAddingFile = () => {
      this.uploaderSubjectElemRef.nativeElement.value = '';
      this.checksFront();
      this.evaluationForm.markAsDirty();
    };
    this.uploaderSubject.onCompleteItem = (item: any, response: any, status: any, headers: any) => {
      response = JSON.parse(response);
      if (response.success) {
        this.evaluation = new Evaluation(response.evaluation);
      } else {
        this.alertService.configWaitingAlert('Un ou plusieurs fichiers de sujet n\'ont pas pu être importés.', 'error');
      }
      this.loaderService.display(false);
    };
    this.uploaderExamples = new FileUploader({
      headers: [{name: 'Authorization' , value: 'Bearer ' + this.authService.getToken()}],
      url: environment.api_url + 'evaluations/' + this.evaluation.id + '/examples',
      removeAfterUpload: true
    });
    this.uploaderExamples.onAfterAddingFile = () => {
      this.uploaderExamplesElemRef.nativeElement.value = '';
      this.checksFront();
      this.evaluationForm.markAsDirty();
    };
    this.uploaderExamples.onCompleteItem = (item: any, response: any, status: any, headers: any) => {
      response = JSON.parse(response);
      if (response.success) {
        this.evaluation = new Evaluation(response.evaluation);
      } else {
        this.alertService.configWaitingAlert('Un ou plusieurs fichiers d\'exemples n\'ont pas pu être importés.', 'error');
      }
      this.loaderService.display(false);
    };
    // SECTION
    this.sectionUpdateActive = false;
    this.sectionUpdated = false;
    this.sectionOrderTmp = null;
    // FORM
    this.getGroups();
    this.setForm();
    // CHECKS
    this.checks();
    this.checksFront(true);
    this.checksBack(true);
  }

  ngAfterViewInit() {
    // WYSIWYG
    if (this.evaluation && this.evaluationForm && Object.keys(this.editors).length === 0) {
      if (this.evaluation.id) {
        this.formService.wysiwyg(this.editors, 'subject', this.subjectControl, this.evaluation.active_assignment);
        this.formService.wysiwyg(this.editors, 'assignmentInstructions', this.assignmentInstructionsControl, this.evaluation.active_assignment);
      }
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
    this.evaluationForm.valueChanges.subscribe(data => { this.checksFront(); });
  }
  checksFront(init: boolean = false) {
    // Function
    if (init) {
      this.saveControls = {
        'name':     [this.nameControl,    {
          'required' : 'Le nom du devoir est requis.'
        }, 'Nom du devoir', null],
        'lesson':   [this.lessonControl,  {
          'required': 'Veuillez sélectionner un cours.',
          'maxNumber:1': 'Vous ne pouvez sélectionner qu\'un seul cours.'
        }, 'Cours du devoir', null],
        'subjectFiles': [['file', this.uploaderSubject, this.filesMaxSizeSetting, this.filesTypesSetting], {
          'fileType': 'Le type de fichier n\'est pas accepté.',
          'fileSize': 'Le fichier est trop volumineux.'
        }, 'Fichiers de sujet', null],
        'assignmentInstructions':  [this.assignmentInstructionsControl, {}, 'Consignes', null],
        'dateStartCorrection' : [this.evaluation.date_start_correction, {}, 'Date de début de correction', null],
        'dateEndCorrection' : [this.evaluation.date_end_correction, {}, 'Date de fin de correction', null],
        'dateStartAssignment' : [[this.dateStartAssignmentControl, this.timeStartAssignmentControl], {
          'maxDate:dateStartAssignment,dateStartCorrection' : 'La date de début de devoir ne peut être supérieure à la date de début de correction.',
        }, 'Date de début des devoirs', null],
        'dateEndAssignment' : [[this.dateEndAssignmentControl, this.timeEndAssignmentControl], {
          'minDate:dateEndAssignment,dateStartAssignment' : 'La date de fin de devoir ne peut être antérieure à la date de début de devoir.',
          'maxDate:dateEndAssignment,dateEndCorrection' : 'La date de fin de devoir ne peut être supérieure à la date de fin de correction.',
        }, 'Date de fin des devoirs', null],
        'individualAssignment' : [this.individualAssignmentControl, {}, 'Mode de travail', null],
        'users':    [this.usersControl,   {}, 'Attribution des étudiants', 'individualAssignment:true'],
        'groups':    [this.groupsControl,   {}, 'Attribution des groupes', 'individualAssignment:false'],
        'exampleAssignments': [['file', this.uploaderExamples, this.filesMaxSizeSetting, this.filesTypesSetting], {
          'fileType': 'Le type de fichier n\'est pas accepté.',
          'fileSize': 'Le fichier est trop volumineux.'
        }, 'Fichiers d\'exemple', null],
        'activeAssignment' : [this.evaluation.active_assignment, {}, '', null],
        'isActivate':   [this.evaluationForm,  {
          'true': ''
        }, '_Le devoir doit être désactivé.', 'activeAssignment:true'],
      };
      this.activateControls = {
        'name':     [this.nameControl,    {
          'required' : 'Le nom du devoir est requis.'
        }, 'Nom du devoir', null],
        'lesson':   [this.lessonControl,  {
          'required': 'Veuillez sélectionner un cours.',
          'maxNumber:1': 'Vous ne pouvez sélectionner qu\'un seul cours.'
        }, 'Cours du devoir', null],
        'subject':  [this.subjectControl, {}, 'Sujet du devoir', null],
        'subjectFiles': [['file', this.uploaderSubject, this.filesMaxSizeSetting, this.filesTypesSetting], {
          'fileType': 'Le type de fichier n\'est pas accepté.',
          'fileSize': 'Le fichier est trop volumineux.'
        }, 'Fichiers de sujet', null],
        'assignmentInstructions':  [this.assignmentInstructionsControl, {}, 'Consignes', null],
        'dateStartCorrection' : [this.evaluation.date_start_correction, {}, 'Date de début de correction', null],
        'dateEndCorrection' : [this.evaluation.date_end_correction, {}, 'Date de fin de correction', null],
        'dateStartAssignment' : [[this.dateStartAssignmentControl, this.timeStartAssignmentControl], {
          'required': 'Veuillez définir une date de début de devoir.',
          'maxDate:dateStartAssignment,dateStartCorrection' : 'La date de début de devoir ne peut être supérieure à la date de début de correction.',
        }, 'Date de début des devoirs', null],
        'dateEndAssignment' : [[this.dateEndAssignmentControl, this.timeEndAssignmentControl], {
          'required': 'Veuillez définir une date de fin de devoir.',
          'minDate:dateEndAssignment,dateStartAssignment' : 'La date de fin de devoir ne peut être antérieure à la date de début de devoir.',
          'maxDate:dateEndAssignment,dateEndCorrection' : 'La date de fin de devoir ne peut être supérieure à la date de fin de correction.',
        }, 'Date de fin des devoirs', null],
        'individualAssignment' : [this.individualAssignmentControl, {
          'required': 'Veuillez sélectionner un mode de travail.'
        }, 'Mode de travail', null],
        'users':    [this.usersControl,   {
          'required': 'Vous devez sélectionner des étudiants.',
          'minNumber:2': '2 étudiants minimum.'
        }, 'Attribution des étudiants', 'individualAssignment:true'],
        'groups':    [this.groupsControl,   {
          'required': 'Vous devez sélectionner des groupes.',
          'minNumber:2': '2 groupes minimum.'
        }, 'Attribution des groupes', 'individualAssignment:false'],
        'exampleAssignments': [['file', this.uploaderExamples, this.filesMaxSizeSetting, this.filesTypesSetting], {
          'fileType': 'Le type de fichier n\'est pas accepté.',
          'fileSize': 'Le fichier est trop volumineux.'
        }, 'Fichiers d\'exemple', null],
        'sections':   [this.sectionsControl,   {
          'minNumber:1': 'Vous devez créer au moins une section.'
        }, 'Sections', null],
        'form':       [this.evaluationForm,     {
          'dirty': ''
        }, '_Vous devez enregistrer les modifications.', null],
      };
    }
    this.saveChecks = this.formService.checkFrontErrors(this.saveControls);
    this.activateChecks = this.formService.checkFrontErrors(this.activateControls);
  }
  checksBack(init: boolean = false, response = []) {
    if (init) {
      this.backControls = [
        'name',
        'lesson',
        'subject',
        'subjectFiles',
        'assignmentInstructions',
        'dateStartAssignment',
        'dateEndAssignment',
        'individualAssignment',
        'users',
        'groups',
        'exampleAssignments',
        'sections'
      ];
    }
    this.backChecks = this.formService.checkBackErrors(this.backControls, response);
  }

  // SELECT
  getLessons(): void {
    this.loaderService.display(true);
    this.lessonsSelectTexts = this.formService.selectTexts(' le cours...');
    this.restService.getDb('userLessons')
      .then(lessons => {
        for (const lesson of lessons) {
          this.lessons.push({id: lesson.id, name: lesson.name });
        }
        this.loaderService.display(false);
      });
  }
  getUsers(): void {
    this.usersSelectTexts = this.formService.selectTexts(' les étudiants...');
    if (this.evaluation.id) {
      for (const user of this.evaluation.lesson.getStudents()) {
        this.users.push({id: user.id, name: user.name() });
      }
    }
  }
  getGroups(): void {
    this.groupsSelectTexts = this.formService.selectTexts(' les groupes...');
    if (this.evaluation.id) {
      for (const group of this.evaluation.lesson.groups) {
        this.groups.push({ id: group.id, name: group.name });
      }
    }
  }

  getMaxSizeSetting(): void {
    this.loaderService.display(true);
    this.restService.getDb('maxSizeSetting')
      .then(maxSizeSetting => this.filesMaxSizeSetting = parseInt(maxSizeSetting.value, 10))
      .then(() => {
        // CHECKS
        this.checksFront(true);
        this.checksBack(true);
        this.loaderService.display(false);
      });
  }

  setForm(): void {
    this.evaluationForm = new FormGroup({
      definition: new FormGroup({
        name: this.nameControl = new FormControl({
            value: this.evaluation.name,
            disabled: this.evaluation.active_assignment
          },
        ),
        lesson: this.lessonControl = new FormControl({
            value: this.evaluation.getLessonId(),
            disabled: this.evaluation.id
          },
          /*Validators.compose([
            Validators.required,
            Validators.maxLength(1)
          ])*/
        ),
        subject: this.subjectControl = new FormControl({
          value: this.evaluation.subject,
          disabled: this.evaluation.active_assignment,
        }),
      }),
      instructions: new FormGroup({
        assignmentInstructions: this.assignmentInstructionsControl = new FormControl(
          this.evaluation.assignment_instructions
        ),
        datesAssignment: this.datesAssignmentGroup = new FormGroup({
            dateStart: this.dateStartAssignmentControl = new FormControl({
              value: this.evaluation.date_start_assignment,
              disabled: this.evaluation.active_assignment,
              },
            ),
            timeStart: this.timeStartAssignmentControl = new FormControl({
              value: this.evaluation.date_start_assignment,
              disabled: this.evaluation.active_assignment,
              },
            ),
            dateEnd: this.dateEndAssignmentControl = new FormControl({
              value: this.evaluation.date_end_assignment,
              disabled: this.evaluation.active_assignment,
              },
            ),
            timeEnd: this.timeEndAssignmentControl = new FormControl({
              value: this.evaluation.date_end_assignment,
              disabled: this.evaluation.active_assignment,
              },
            )
          },
          /*Validators.compose([
            DateRangeValidator(),
            DateMaxValidator(new FormControl(this.evaluation.date_start_correction),
              new FormControl(this.evaluation.date_start_correction), 'End')
          ])*/
        ),
      }),
      attribution: new FormGroup({
        individualAssignment: this.individualAssignmentControl = new FormControl({
          value: this.evaluation.individual_assignment,
          disabled: this.evaluation.active_assignment
          },
        ),
        users: this.usersControl = new FormControl({
            // this.evaluation.id ? this.evaluation.lesson.getStudentsId() : []
            value: this.evaluation.id ? this.evaluation.getUsersId() : [],
            disabled: this.evaluation.active_assignment
          },
          // LenMinValidator(1, !this.individualAssignmentControl.value)
        ),
        groups: this.groupsControl = new FormControl({
            // this.evaluation.id ? this.evaluation.lesson.getGroupsId() : []
            value: this.evaluation.id ? this.evaluation.getGroupsId() : [],
            disabled: this.evaluation.active_assignment
          },
          // LenMinValidator(1, this.individualAssignmentControl.value)
        ),
      }),
      exampleAssignments: this.exampleAssignmentsControl = new FormControl(
        ''
      ),
      sections: this.sectionsControl = new FormControl({
        value: this.evaluation.id ? this.evaluation.getSectionsId() : [],
        disabled: this.evaluation.active_assignment
      })
    });
  }

  // SECTION
  moveSection(sectionToMove: Section, move: number): void {
    this.sectionUpdated = true;
    this.evaluation.sections.find(section => section.order === sectionToMove.order + move).order -= move;
    sectionToMove.order += move;
    this.filterService.sortList(this.evaluation.sections, 'order', true);
    this.sectionsControl.markAsDirty();
    this.checksFront();
  }
  deleteSection(sectionToDelete: Section): void {
    const index: number = this.evaluation.sections.indexOf(sectionToDelete);
    const order: number = this.evaluation.sections[index].order;

    this.sectionUpdated = true;
    this.evaluation.sections.splice(index, 1);
    const sectionsId = [];
    this.evaluation.sections.forEach(section => {
      sectionsId.push(section.id);
      if (section.order >= order) {
        section.order--;
      }
    });
    this.sectionsControl.setValue(sectionsId);
    this.sectionsControl.markAsDirty();
    this.checksFront();
  }
  displaySectionForm(section?: Section) {
    if (!isUndefined(section) && section.id == null) {
      section.id = 0;
    }
    section = ( isUndefined(section) ? new Section() : section );
    this.section_tmp = section;
    this.view_section_form = true;
    this.sectionsControl.markAsTouched();
    this.sectionsControl.markAsDirty();
  }

  // EVALUATION
  cancel(): void {
    let status: string;

    status = this.evaluationService.getEvaluationStatus(this.evaluation);
    this.router.navigate(['/teacher', 'evaluations', status]);
  }
  saveEvaluation(): void {
    const newEvaluation = this.evaluation.id ? false : true;
    if (this.formService.checkEmptyChecks(this.saveChecks)) {
      this.loaderService.display(true);
      this.applyFormData();
      (this.evaluation.id ? this.restService.updateDb('evaluation', this._evaluation) : this.restService.addDb('evaluations', this._evaluation))
        .then(response => {
          if (response.success) {
            this.evaluationForm.markAsPristine();
            this.dirtyChange.emit(this.evaluationForm.dirty);
            if (!newEvaluation) {
              // EVAL
              this.getEntity(response.evaluation);
              // FILES
              this.formService.upload([this.uploaderSubject, this.uploaderExamples]);
            }
          } else {
            this.checksBack(false, response.errors);
            this.alertService.configWaitingAlert('Impossible d\'enregistrer. Vérifier les champs en rouge.', 'error');
          }
          return response;
        })
        .then(response => {
          if (response.success) {
            // CHECKS
            this.checksBack();
            // ALERT
            if (newEvaluation) {
              this.alertService.configWaitingAlert('L\'évaluation a bien été créée.');
              this.router.navigate(['/teacher', 'evaluation', response.evaluation.id, 'edit']);
            } else {
              this.alertService.configWaitingAlert('L\'évaluation a bien été enregistrée.');
            }
          }
          // CHECKS
          this.sectionUpdated = false;
          this.evaluationForm.markAsPristine();
          this.evaluationForm.markAsUntouched();
          if (!response.success) {
            this.assignmentInstructionsControl.markAsDirty();
            console.log(this.evaluationForm);
          }
          this.checksFront();
          // LOADER
          this.loaderService.display(false);
        })
        .catch(() => {
          console.error(`Evaluation \'${this.evaluation.name}\' cannot be changed in the database.`);
          this.alertService.configWaitingAlert('Une erreur est survenue...', 'error');
          this.loaderService.display(false);
        });
    }
  }
  // TOGGLE (ACTIVATION/DEACTIVATION)
  toggleAssignment(display_alert: boolean = true) {
    this.activeCorrection_tmp = this.evaluation.active_correction;
    if (this.formService.checkEmptyChecks(this.activateChecks)) {
      this.loaderService.display(true);
      this.restService.updateDb('evaluation_toggle', this.evaluation)
        .then(response => {
          if (response.success) {
            if (response.warning && !confirm('Attention, des étudiants ont déjà rendu leur devoir. Si vous effectuez des modifications vous risquez de supprimer leur travail! Êtes-vous sur de continuer ?')) {
              response.success = false;
              this.toggleAssignment(false);
              if (this.activeCorrection_tmp) {
                this.restService.updateDb('correction_toggle', this.evaluation);
              }
            } else {
              this.getEntity(response.evaluation);
            }
          } else {
            this.checksBack(false, response.errors);
            this.alertService.configWaitingAlert('Impossible d' + (this.evaluation.active_assignment ? 'e dés' : '\'') + 'activer. Vérifier les champs en rouge.', 'error');
          }
          return response;
        })
        .then(response => {
          if (response.success) {
            // FORM
            this.evaluation.active_assignment ? this.evaluationForm.disable() : this.evaluationForm.enable();
            this.evaluation.id ? this.lessonControl.disable() : null;
            // WYSIWYG
            this.ngOnDestroy();
            this.ngAfterViewInit();
            // RESET backChecks
            this.checksBack();
            // ALERT
            display_alert ? this.alertService.configWaitingAlert('L\'évaluation a bien été ' + (response.evaluation.active_assignment ? '' : 'dés')  + 'activée.') : null;
          }
          // CHECKS
          this.sectionUpdated = false;
          this.evaluationForm.markAsPristine();
          this.evaluationForm.markAsUntouched();
          this.checksFront(true);
          // LOADER
          this.loaderService.display(false);
        });
    }
  }

  // FILES
  removeFile(type: string, id: number) {
    this.loaderService.display(true);
    this.restService.deleteDb('evaluation_' + type, [this.evaluation.id, id])
      .then(response => {
        response.success ? this.evaluation = new Evaluation(response.evaluation) : null;
        this.loaderService.display(false);
      });
  }
  removeQueuedItem(item: any): void {
    item.remove();
    this.checksFront();
  }

  //
  getEntity(entity) {
    this.evaluation = new Evaluation(entity);
    this.filterService.sortList(this.evaluation.sections, 'order', true);
  }
  applyFormData(): void {
    this._evaluation = Object.assign({}, this.evaluation);
    // DEFINITION
    this._evaluation.name = this.nameControl.value;
    this._evaluation.lesson = this.lessonControl.value !== null ? this.lessonControl.value[0] : null;
    this._evaluation.subject = this.subjectControl.value;
    // ASSIGNMENT
    this._evaluation.assignment_instructions = this.assignmentInstructionsControl.value;
    if (this.dateStartAssignmentControl.value !== null) {
      this._evaluation.date_start_assignment = joinDateAndTime(this.dateStartAssignmentControl.value, this.timeStartAssignmentControl.value);
    } else if (this.evaluation.date_start_assignment !== null) {
      this._evaluation.date_start_assignment = null;
    }
    if (this.dateEndAssignmentControl.value !== null) {
      this._evaluation.date_end_assignment = joinDateAndTime(this.dateEndAssignmentControl.value, this.timeEndAssignmentControl.value);
    } else if (this.evaluation.date_end_assignment !== null) {
      this._evaluation.date_end_assignment = null;
    }
    // ATTRIBUTION
    this._evaluation.individual_assignment = this.individualAssignmentControl.value;
    if (this._evaluation.individual_assignment) {
      this._evaluation.users = this.usersControl.value;
      this._evaluation.groups = [];
    } else {
      this._evaluation.groups = this.groupsControl.value;
      this._evaluation.users = [];
    }
    // EXAMPLES : Nothing because only files
    // SECTIONS
    this._evaluation.sections = [];
    this.evaluation.sections.forEach((section, sectionIndex) => {
      this._evaluation.sections.push(Object.assign({}, section));
      this._evaluation.sections[sectionIndex].section_type = this._evaluation.sections[sectionIndex].section_type.id;
      this._evaluation.sections[sectionIndex].file_types.forEach((fileType, fileTypeIndex) => {
        this._evaluation.sections[sectionIndex].file_types[fileTypeIndex] = fileType.id;
      });
    });
  }

}

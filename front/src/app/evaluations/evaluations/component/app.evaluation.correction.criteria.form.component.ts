import { AfterViewChecked, AfterViewInit, Component, EventEmitter, Input, OnDestroy, OnInit, Output } from '@angular/core';
import { FormGroup, FormControl, Validators } from '@angular/forms';

// Classes
import { Criteria, Evaluation, Section, CriteriaType } from '../../class/app.evaluation.class';

// Animations
import { slideInOutAnimation } from '../../../../animations/slide.animations';

// Services
import { AppCoreRestService } from '../../../core/service/app.core.rest.service';
import { AppCoreFormService } from '../../../core/service/app.core.form.service';
import { AppAuthAuthenticationService } from '../../../auth/service/app.auth.authentication.service';
import { AppCoreLoaderService } from '../../../core/service/app.core.loader.service';

// Validators
import { StepValidator } from '../../../core/validator/app.core.step.validator';

// -----

@Component ({
  selector: 'app-evaluation-correction-criteria-form',
  templateUrl: '../html/app.evaluation.correction.criteria.form.component.html',
  styleUrls: ['../../../../assets/css/app.evaluation.correction.criteria.form.component.scss'],
  animations: [slideInOutAnimation],
  host: { '[@slideInOutAnimation]': '' }
})
export class AppEvaluationCorrectionCriteriaFormComponent implements OnInit, OnDestroy, AfterViewInit, AfterViewChecked {

  @Input() evaluation: Evaluation;
  @Input() section: Section;
  @Input() criteria: Criteria;
  @Input() getViewCriteriaForm: Boolean;
  @Input() criteriasControl: FormControl;
  @Output() evaluationChange = new EventEmitter();
  @Output() getViewCriteriaFormChange = new EventEmitter();
  @Output() criteriasControlChange = new EventEmitter();
  // FORM
  criteriaForm: FormGroup;
  invalidForm = false;
  // FORM CONTROLS
  criteriaTypeControl: FormControl;
  criteriaDescriptionControl: FormControl;
  criteriaWeightControl: FormControl;
  criteriaMarkMinControl: FormControl;
  criteriaMarkMaxControl: FormControl;
  criteriaPrecisionControl: FormControl;
  criteriaChoicesControl: FormControl;
  // WYSIWYG
  editors = {};
  // ERRORS
  backChecks = null;
  saveChecks = null;
  backControls = {};
  saveControls = {};

  criteriaTypes: CriteriaType[];
  criteriaOrderTmp: number;

  constructor(
    private restService: AppCoreRestService,
    private authService: AppAuthAuthenticationService,
    private loaderService: AppCoreLoaderService,
    private formService: AppCoreFormService
  ) {
    console.log('__CONSTRUCT__ app.evaluation.correction.criteria.form.component');
    this.authService.checkRole(['teacher'], true);
  }

  ngOnInit(): void {
    this.invalidForm = false;
    // RADIO
    this.getCriteriaTypes();
    // FORM
    this.setForm();
    // CHECKS
    this.checks();
    this.checksFront(true);
    this.checksBack(true);
  }
  // WYSIWYG
  ngAfterViewInit() {
    if (this.evaluation && this.criteriaForm && Object.keys(this.editors).length === 0) {
      this.formService.wysiwyg(this.editors, 'criteriaDescription', this.criteriaDescriptionControl, this.evaluation.active_correction);
    }
  }
  ngAfterViewChecked() {
    this.ngAfterViewInit();
  }
  ngOnDestroy() {
    this.editors = this.formService.wysiwyg_remove(this.editors);
  }
  toggleEditor() {
    this.criteriaDescriptionControl.reset(this.criteriaDescriptionControl.value);
  }

  //CHECKS
  checks() {
    this.criteriaForm.valueChanges.subscribe(() => { this.checksFront(); });
  }
  checksFront(init: boolean = false) {
    // Function
    if (init) {
      this.saveControls = {
        'criteriaType':     [this.criteriaTypeControl,    {
          'required' : 'Le type de critère est requis.'
        }, 'Type', null],
        'criteriaDescription':     [this.criteriaDescriptionControl,    {
          'required' : 'La description du critère est requise.'
        }, 'Description', null],
        'choices':   [this.criteriaChoicesControl,   {
          'minNumber:2': 'Vous devez créer au moins deux choix.'
        }, 'Choix', 'criteriaType:2'],
        'criteriaWeight':     [this.criteriaWeightControl,    {
          'required' : 'Le poids du critère est requis.',
          'min:0': 'Le poids doit être supérieur à 0.',
        }, 'Poids', 'criteriaType:2||criteriaType:3'],
        'markMin' : [this.criteriaMarkMinControl, {}, 'Note minimale', null],
        'markMax' : [this.criteriaMarkMaxControl, {}, 'Note maximale', null],
        'criteriaMarkMin':     [this.criteriaMarkMinControl,    {
          'required' : 'La note minimale du critère est requis.',
          'max:markMax': 'La note minimale doit être inférieure à la note maximale',
        }, 'Note minimale', 'criteriaType:3'],
        'criteriaMarkMax':     [this.criteriaMarkMaxControl,    {
          'required' : 'La note maximale du critère est requis.',
          'min:markMin': 'La note maximale doit être supérieure à la note minimale',
        }, 'Note maximale', 'criteriaType:3'],
        'criteriaPrecision':     [this.criteriaPrecisionControl,    {
          'required' : 'La précision du critère est requis.',
          'min:0': 'La précision doit être supérieure à 0.',
          'max:1': 'La précision doit être inférieure à 1.',
        }, 'Précision', 'criteriaType:3'],
      };
    }
    this.saveChecks = this.formService.checkFrontErrors(this.saveControls);
  }
  checksBack(init: boolean = false, response = []) {
    if (init) {
      this.backControls = [
        'criteriaType',
        'criteriaDescription',
        'criteriaChoices',
        'criteriaWeight',
        'criteriaMarkMin',
        'criteriaMarkMax',
        'criteriaPrecision'
      ];
    }
    this.backChecks = this.formService.checkBackErrors(this.backControls, response);
  }

  // RADIO
  getCriteriaTypes(): void {
    this.loaderService.display(true);
    this.criteriaTypes = [];
    this.restService.getDb('criteriaTypes')
      .then(criteriaTypes => criteriaTypes.forEach(criteriaType => {
        this.criteriaTypes.push(new CriteriaType(criteriaType));
        this.loaderService.display(false);
      }));
  }

  setForm(): void {
    this.criteriaForm = new FormGroup({
      type: this.criteriaTypeControl = new FormControl(
        this.criteria.id || this.criteria.id === 0 ? this.criteria.criteria_type.id : 1,
        //Validators.required
      ),
      description: this.criteriaDescriptionControl = new FormControl(
        this.criteria.id || this.criteria.id === 0 ? this.criteria.description : '',
        //Validators.required
      ),
      weight: this.criteriaWeightControl = new FormControl(
        this.criteria.weight,
        /*Validators.compose([
          Validators.required,
          Validators.min(0),
          StepValidator(1)
        ])*/
      ),
      markMin: this.criteriaMarkMinControl = new FormControl(
        this.criteria.mark_min,
        /*Validators.compose([
          Validators.required,
          StepValidator(1)
        ])*/
      ),
      markMax: this.criteriaMarkMaxControl = new FormControl(
        this.criteria.id || this.criteria.id === 0 ? this.criteria.mark_max : null,
        /*Validators.compose([
          Validators.required,
          Validators.min(0),
          Validators.min(this.criteriaMarkMinControl.value),
          StepValidator(1)
        ])*/
      ),
      precision: this.criteriaPrecisionControl = new FormControl(
        this.criteria.id || this.criteria.id === 0 ? this.criteria.precision : null,
        /*Validators.compose([
          Validators.required,
          Validators.min(0),
          Validators.max(1)
        ])*/
      ),
      choices: this.criteriaChoicesControl = new FormControl(
        this.criteria.id ? this.criteria.getChoicesId() : []
      )
    });
  }

  // SECTION
  cancel(): void {
    this.getViewCriteriaForm = false;
    this.getViewCriteriaFormChange.emit(false);
    // CHECKS
    this.criteriaForm.markAsPristine();
    this.criteriaForm.markAsUntouched();
    this.checksFront();
  }
  save(): void {
    if (this.formService.checkEmptyChecks(this.saveChecks)) {
      this.applyFormData();
      const index_section = this.evaluation.sections.findIndex(section => section.id === this.section.id);
      // UPDATE
      if (this.criteria.id || this.criteria.id === 0) {
        const index_criteria = this.evaluation.sections[index_section].criterias.findIndex(criteria => criteria.id === this.criteria.id);
        this.evaluation.sections[index_section].criterias[index_criteria] = this.criteria;
        // NEW
      } else {
        this.evaluation.sections[index_section].criterias.push(this.criteria);
      }
      if (this.criteria.id === 0) {
        this.criteria.id = null;
      }
      this.evaluationChange.emit(this.evaluation);
      // CriteriasControl
      const criteriasId = this.evaluation.getCriteriasId();
      criteriasId[index_section] = [];
      this.evaluation.sections[index_section].criterias.forEach(criteria => {
        criteriasId[index_section].push(criteria.id);
      });
      this.criteriasControl.setValue(criteriasId);
      this.criteriasControlChange.emit(this.criteriasControl);
      this.cancel();
    }
  }

  isFormValid(): boolean {
    return ((this.criteriaTypeControl.value == 1 && this.criteriaDescriptionControl.valid)
      || (this.criteriaTypeControl.value == 2 && this.criteriaDescriptionControl.valid
        && this.criteriaWeightControl.valid && this.criteria.criteria_choices.length > 1)
      || (this.criteriaTypeControl.value == 3 && this.criteriaForm.valid && this.criteriaWeightControl.valid));
  }

  //
  applyFormData(): void {
    if (this.criteriaTypeControl.value == 1 && this.criteriaDescriptionControl.valid) {
      this.criteria.criteria_type = this.criteriaTypes.find(type => type.id === this.criteriaTypeControl.value);
      this.criteria.description = this.criteriaDescriptionControl.value;
      this.criteria.criteria_choices = [];
      this.criteria.order = !this.criteria.order ? this.section.criterias.length + 1 : this.criteria.order;
    }
    if (this.criteriaTypeControl.value == 2 && this.criteriaDescriptionControl.valid
      && this.criteriaWeightControl.valid && this.criteria.criteria_choices.length > 1) {
      this.criteria.criteria_type = this.criteriaTypes.find(type => type.id === this.criteriaTypeControl.value);
      this.criteria.description = this.criteriaDescriptionControl.value;
      this.criteria.weight = this.criteriaWeightControl.value;
      this.criteria.order = !this.criteria.order ? this.section.criterias.length + 1 : this.criteria.order;
    }
    if (this.criteriaTypeControl.value == 3 && this.criteriaForm.valid && this.criteriaWeightControl.valid) {
      this.criteria.criteria_type = this.criteriaTypes.find(type => type.id === this.criteriaTypeControl.value);
      this.criteria.description = this.criteriaDescriptionControl.value;
      this.criteria.weight = this.criteriaWeightControl.value;
      this.criteria.mark_min = this.criteriaMarkMinControl.value;
      this.criteria.mark_max = this.criteriaMarkMaxControl.value;
      this.criteria.precision = this.criteriaPrecisionControl.value;
      this.criteria.criteria_choices = [];
      this.criteria.order = !this.criteria.order ? this.section.criterias.length + 1 : this.criteria.order;
    }
  }

}

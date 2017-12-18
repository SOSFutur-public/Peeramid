import { AfterViewChecked, AfterViewInit, Component, OnDestroy, OnInit } from '@angular/core';
import { FormGroup, FormControl } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { BsDatepickerConfig } from 'ngx-bootstrap/datepicker';
import { TimepickerConfig } from 'ngx-bootstrap';
import { isUndefined } from 'ngx-bootstrap/bs-moment/utils/type-checks';

// Environment
import { environment } from '../../../../environments/environment';

// Classes
import { Criteria, Evaluation, Section, Assignment } from '../../class/app.evaluation.class';

// Animations
import { routerAnimation } from '../../../../animations/router.animations';

// Functions
import { joinDateAndTime } from '../../../core/functions/app.core.utils.functions';

// Services
import { AppCoreRestService } from '../../../core/service/app.core.rest.service';
import { AppCoreFilterService } from '../../../core/service/app.core.filter.service';
import { AppCoreAlertService } from '../../../core/service/app.core.alert.service';
import { AppCoreFormService } from '../../../core/service/app.core.form.service';
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
  selector: 'app-evaluation-correction-form',
  templateUrl: '../html/app.evaluation.correction.form.component.html',
  styleUrls: ['../../../../assets/css/app.evaluation.correction.form.component.scss'],
  animations: [routerAnimation],
  host: { '[@routerAnimation]': '' },
  providers: [{provide: TimepickerConfig, useFactory: getTimepickerConfig}]
})
export class AppEvaluationCorrectionFormComponent implements OnInit, OnDestroy, AfterViewInit, AfterViewChecked {

  // ENV
  environment = environment;
  // VAR
  evaluation: Evaluation;
  _evaluation: any;
  attributions: Assignment[] = [];
  // FORM
  correctionForm: FormGroup;
  invalidForm = false;
  // FORM GROUPS
  datesCorrectionGroup: FormGroup;
  datesOpinionGroup: FormGroup;
  // FORM CONTROLS
  correctionInstructionsControl: FormControl;
  dateStartCorrectionControl: FormControl;
  dateEndCorrectionControl: FormControl;
  timeStartCorrectionControl: FormControl;
  timeEndCorrectionControl: FormControl;
  dateEndOpinionControl: FormControl;
  timeEndOpinionControl: FormControl;
  individualCorrectionControl: FormControl;
  anonymityControl: FormControl;
  numberCorrectionsControl: FormControl;
  criteriasControl: FormControl;
  // DATETIME
  currentDate: Date = new Date();
  minDate: Date = new Date();
  locale = 'fr';
  _bsValue: Date;
  bsConfig: Partial<BsDatepickerConfig>;
  // WYSIWYG
  editors = {};
  // VIEWS
  view_attributions: Boolean = false;
  view_criteria_form: Boolean = false;
  section_tmp: Section;
  criteria_tmp: Criteria;
  criteriaUpdated = false;
  // ERRORS
  backChecks = null;
  saveChecks = null;
  activateChecks = null;
  repartitionChecks = null;
  backControls = {};
  saveControls = {};
  activateControls = {};
  repartitionControls = {};


  constructor(
    private route: ActivatedRoute,
    private router: Router,
    private restService: AppCoreRestService,
    private filterService: AppCoreFilterService,
    private authService: AppAuthAuthenticationService,
    private loaderService: AppCoreLoaderService,
    private alertService: AppCoreAlertService,
    private formService: AppCoreFormService,
    private evaluationService: AppEvaluationService
  ) {
    console.log('__CONSTRUCT__ app.evaluation.correction.form.component');
    this.authService.checkRole(['teacher'], true);
    // Current Date
    setInterval(() => {
      this.currentDate =  new Date();
    }, 1000);
  }

  canDeactivate() {
    if (this.correctionForm && this.correctionForm.dirty) {
      return confirm('Êtes-vous sûr de quitter le formulaire sans enregistrer vos modifications?');
    }
    return true;
  }

  ngOnInit(): void {
    // DATETIME PICKER
    this.bsConfig = Object.assign({}, {
      locale: this.locale,
      showWeekNumbers: true,
    });
    // Evaluation
    this.getEvaluation();
    // CHECKS
    //this.checksFront(true);
    //this.checksBack(true);
  }

  // WYSIWYG
  ngAfterViewInit() {
    if (this.evaluation && this.correctionForm && Object.keys(this.editors).length === 0) {
      this.formService.wysiwyg(this.editors, 'correctionInstructions', this.correctionInstructionsControl, this.evaluation.active_correction);
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
    this.correctionForm.valueChanges.subscribe(data => { this.checksFront(); });
  }
  checksFront(init: boolean = false) {
    if (init) {
      this.saveControls = {
        'correctionInstructions':     [this.correctionInstructionsControl,    {}, 'Instructions', null],
        'dateStartAssignment' : [this.evaluation.date_start_assignment, {}, 'Date de début de devoir', null],
        'dateEndAssignment' : [this.evaluation.date_end_assignment, {}, 'Date de fin de devoir', null],
        'dateStartCorrection' : [[this.dateStartCorrectionControl, this.timeStartCorrectionControl], {
          'minDate:dateStartCorrection,dateStartAssignment' : 'La date de début de correction ne peut être antérieure à la date de début de devoir.',
        }, 'Date de début des corrections', null],
        'dateEndCorrection' : [[this.dateEndCorrectionControl, this.timeEndCorrectionControl], {
          'minDate:dateEndCorrection,dateStartCorrection' : 'La date de fin de correction ne peut être antérieure à la date de début de correction.',
          'minDate:dateEndCorrection,dateEndAssignment' : 'La date de fin de correction ne peut être antérieure à la date de fin de devoir.',
        }, 'Date de fin des corrections', null],
        'dateEndOpinion' : [[this.dateEndOpinionControl, this.timeEndOpinionControl], {
          'minDate:dateEndOpinion,dateEndCorrection' : 'La date de fin d\'opinion ne peut être antérieure à la date de fin de correction.',
        }, 'Date de fin des opinions', null],
        'numberUsers' : [this.evaluation.users.length - 1, {}, 'Nombre d\'étudiants moins 1', null],
        'numberGroups' : [this.evaluation.groups.length - 1, {}, 'Nombre de groupes moins 1', null],
        'individualCorrection' : [this.individualCorrectionControl, {}, 'Mode de correction', null],
        'anonymity' : [this.anonymityControl, {}, 'Nature de la correction', null],
        'individualAssignment' : [this.evaluation.individual_assignment, {}, 'Mode de travail', null],
        'numberCorrectionsUsers' : [this.numberCorrectionsControl, {
          'regex:^[0-9.,]+$' : 'Veuillez entrer un nombre.',
          'integer': 'Veuillez entrez un nombre entier.',
          'min:1': 'Le nombre de corrections doit être supérieur ou égal à 1.',
          'max:numberUsers': 'Le nombre de corrections ne doit pas être supérieur à ' + (this.evaluation.users.length - 1).toString() + '.',
        }, 'Nombre de corrections', 'individualAssignment:true'],
        'numberCorrectionsGroups' : [this.numberCorrectionsControl, {
          'regex:^[0-9.,]+$' : 'Veuillez entrer un nombre.',
          'integer': 'Veuillez entrez un nombre entier.',
          'min:1': 'Le nombre de corrections doit être supérieur ou égal à 1.',
          'max:numberGroups': 'Le nombre de corrections ne doit pas être supérieur à ' + (this.evaluation.groups.length - 1).toString() + '.',
        }, 'Nombre de corrections', 'individualAssignment:false'],
        'criterias':   [this.criteriasControl,   {}, 'Critères', null],
        'activeCorrection' : [this.evaluation.active_correction, {}, '', null],
        'isActivate':   [this.correctionForm,  {
          'true': ''
        }, '_La correction doit être désactivée.', 'activeCorrection:true'],
      };
      this.repartitionControls = {
        'correctionInstructions':     [this.correctionInstructionsControl,    {}, 'Instructions', null],
        'dateStartAssignment' : [this.evaluation.date_start_assignment, {}, 'Date de début de devoir', null],
        'dateEndAssignment' : [this.evaluation.date_end_assignment, {}, 'Date de fin de devoir', null],
        'dateStartCorrection' : [[this.dateStartCorrectionControl, this.timeStartCorrectionControl], {
          'required': 'Veuillez définir une date de début de correction.',
          'minDate:dateStartCorrection,dateStartAssignment' : 'La date de début de correction ne peut être antérieure à la date de début de devoir.',
        }, 'Date de début des corrections', null],
        'dateEndCorrection' : [[this.dateEndCorrectionControl, this.timeEndCorrectionControl], {
          'required': 'Veuillez définir une date de fin de correction.',
          'minDate:dateEndCorrection,dateStartCorrection' : 'La date de fin de correction ne peut être antérieure à la date de début de correction.',
          'minDate:dateEndCorrection,dateEndAssignment' : 'La date de fin de correction ne peut être antérieure à la date de fin de devoir.',
        }, 'Date de fin des corrections', null],
        'dateEndOpinion' : [[this.dateEndOpinionControl, this.timeEndOpinionControl], {
          'required': 'Veuillez définir une date de fin d\'opinion.',
          'minDate:dateEndOpinion,dateEndCorrection' : 'La date de fin d\'opinion ne peut être antérieure à la date de fin de correction.',
        }, 'Date de fin des opinions', null],
        'numberUsers' : [this.evaluation.users.length - 1, {}, 'Nombre d\'étudiants moins 1', null],
        'numberGroups' : [this.evaluation.groups.length - 1, {}, 'Nombre de groupes moins 1', null],
        'individualCorrection' : [this.individualCorrectionControl, {
          'required': 'Veuillez sélectionner un mode de correction.'
        }, 'Mode de correction', null],
        'anonymity' : [this.anonymityControl, {
          'required': 'Veuillez sélectionner la nature de la correction.'
        }, 'Nature de la correction', null],
        'individualAssignment' : [this.evaluation.individual_assignment, {}, 'Mode de travail', null],
        'numberCorrectionsUsers' : [this.numberCorrectionsControl, {
          'required': 'Veuillez indiquer le nombre de corrections à faire.',
          'regex:^[0-9.,]+$' : 'Veuillez entrer un nombre.',
          'integer': 'Veuillez entrez un nombre entier.',
          'min:1': 'Le nombre de corrections doit être supérieur ou égal à 1.',
          'max:numberUsers': 'Le nombre de corrections ne doit pas être supérieur à ' + (this.evaluation.users.length - 1).toString() + '.',
        }, 'Nombre de corrections', 'individualAssignment:true'],
        'numberCorrectionsGroups' : [this.numberCorrectionsControl, {
          'required': 'Veuillez indiquer le nombre de corrections à faire.',
          'regex:^[0-9.,]+$' : 'Veuillez entrer un nombre.',
          'integer': 'Veuillez entrez un nombre entier.',
          'min:1': 'Le nombre de corrections doit être supérieur ou egal à 1.',
          'max:numberGroups': 'Le nombre de corrections ne doit pas être supérieur à ' + (this.evaluation.groups.length - 1).toString() + '.',
        }, 'Nombre de corrections', 'individualAssignment:false'],
        'criterias':   [this.criteriasControl,   {
          'minNumber:1': 'Vous devez créer au moins un critère par section.'
        }, 'Critères', null],
        'form':       [this.correctionForm,     {
          'dirty': ''
        }, '_Vous devez enregistrer les modifications.', null],
        'activeAssignment' : [this.evaluation.active_assignment, {}, 'Etat du devoir', null],
        'condition':       [this.correctionForm,     {
          'true': ''
        }, '_Le devoir doit être activé.', 'activeAssignment:false'],
        'activeCorrection' : [this.evaluation.active_correction, {}, '', null],
        'isActivate':   [this.correctionForm,  {
          'true': ''
        }, '_La correction doit être désactivée.', 'activeCorrection:true'],
      };
      this.activateControls = {
        'correctionInstructions':     [this.correctionInstructionsControl,    {}, 'Instructions', null],
        'dateStartAssignment' : [this.evaluation.date_start_assignment, {}, 'Date de début de devoir', null],
        'dateEndAssignment' : [this.evaluation.date_end_assignment, {}, 'Date de fin de devoir', null],
        'dateStartCorrection' : [[this.dateStartCorrectionControl, this.timeStartCorrectionControl], {
          'required': 'Veuillez définir une date de début de correction.',
          'minDate:dateStartCorrection,dateStartAssignment' : 'La date de début de correction ne peut être antérieure à la date de début de devoir.',
        }, 'Date de début des corrections', null],
        'dateEndCorrection' : [[this.dateEndCorrectionControl, this.timeEndCorrectionControl], {
          'required': 'Veuillez définir une date de fin de correction.',
          'minDate:dateEndCorrection,dateStartCorrection' : 'La date de fin de correction ne peut être antérieure à la date de début de correction.',
          'minDate:dateEndCorrection,dateEndAssignment' : 'La date de fin de correction ne peut être antérieure à la date de fin de devoir.',
        }, 'Date de fin des corrections', null],
        'dateEndOpinion' : [[this.dateEndOpinionControl, this.timeEndOpinionControl], {
          'required': 'Veuillez définir une date de fin d\'opinion.',
          'minDate:dateEndOpinion,dateEndCorrection' : 'La date de fin d\'opinion ne peut être antérieure à la date de fin de correction.',
        }, 'Date de fin des opinions', null],
        'numberUsers' : [this.evaluation.users.length - 1, {}, 'Nombre d\'étudiants moins 1', null],
        'numberGroups' : [this.evaluation.groups.length - 1, {}, 'Nombre de groupes moins 1', null],
        'individualCorrection' : [this.individualCorrectionControl, {
          'required': 'Veuillez sélectionner un mode de correction.'
        }, 'Mode de correction', null],
        'anonymity' : [this.anonymityControl, {
          'required': 'Veuillez sélectionner la nature de la correction.'
        }, 'Nature de la correction', null],
        'individualAssignment' : [this.evaluation.individual_assignment, {}, 'Mode de travail', null],
        'numberCorrectionsUsers' : [this.numberCorrectionsControl, {
          'required': 'Veuillez indiquer le nombre de corrections à faire.',
          'regex:^[0-9.,]+$' : 'Veuillez entrer un nombre.',
          'integer': 'Veuillez entrez un nombre entier.',
          'min:1': 'Le nombre de corrections doit être supérieur à ou égal à 1.',
          'max:numberUsers': 'Le nombre de corrections ne doit pas être supérieur à ' + (this.evaluation.users.length - 1).toString() + '.',
        }, 'Nombre de corrections', 'individualAssignment:true'],
        'numberCorrectionsGroups' : [this.numberCorrectionsControl, {
          'required': 'Veuillez indiquer le nombre de corrections à faire.',
          'regex:^[0-9.,]+$' : 'Veuillez entrer un nombre.',
          'integer': 'Veuillez entrez un nombre entier.',
          'min:1': 'Le nombre de corrections doit être supérieur ou égal à 1.',
          'max:numberGroups': 'Le nombre de corrections ne doit pas être supérieur à ' + (this.evaluation.groups.length - 1).toString() + '.',
        }, 'Nombre de corrections', 'individualAssignment:false'],
        'criterias':   [this.criteriasControl,   {
          'minNumber:1': 'Vous devez créer au moins un critère par section.'
        }, 'Critères', null],
        'form':       [this.correctionForm,     {
          'dirty': ''
        }, '_Vous devez enregistrer les modifications.', null],
        'activeAssignment' : [this.evaluation.active_assignment, {}, 'Etat du devoir', null],
        'condition':       [this.correctionForm,     {
          'true': ''
        }, '_Le devoir doit être activé.', 'activeAssignment:false'],
      };
    }
    this.saveChecks = this.formService.checkFrontErrors(this.saveControls);
    this.activateChecks = this.formService.checkFrontErrors(this.activateControls);
    this.repartitionChecks = this.formService.checkFrontErrors(this.repartitionControls);
    console.log(this.saveChecks);
  }
  checksBack(init: boolean = false, response = []) {
    if (init) {
      this.backControls = [
        'correctionInstructions',
        'dateStartCorrection',
        'dateEndCorrection',
        'individualCorrection',
        'anonymity',
        'numberCorrections',
        'dateEndOpinion',
        'sections',
        'criterias'
      ];
    }
    this.backChecks = this.formService.checkBackErrors(this.backControls, response);
  }

  // GET
  getEvaluation(): void {
    this.loaderService.display(true);
    this.restService.getDb('evaluations', [+this.route.snapshot.params['id']])
      .then(evaluation => this.evaluation = new Evaluation(evaluation))
      .then(() => console.log(this.evaluation))
      .then(() => {
        this.filterService.sortList(this.evaluation.sections, 'order', true);
        this.evaluation.sections.forEach(section => this.filterService.sortList(section.criterias, 'order', true));
        this.getAttributions();
      })
      .then(() => this.setForm())
      .then(() => {
        // CHECKS
        this.checks();
        this.checksFront(true);
        this.checksBack(true);
        // LOADER
        this.loaderService.display(false);
      });
  }
  getAttributions(request?): void {
    this.attributions = [];
    if (isUndefined(request)) {
      this.loaderService.display(true);
      this.restService.getDb('correctionAttributions', [this.evaluation.id])
        .then(attributions => {
          attributions.forEach(attribution => {
            attribution.corrections.length > 0 ? this.attributions.push(new Assignment(attribution)) : null;
          });
          this.loaderService.display(false);
        });
    } else {
      if (request.success) {
        request.assignments.forEach(attribution => {
          console.log(attribution);
          attribution.corrections.length > 0 ? this.attributions.push(new Assignment(attribution)) : null;
        });
      }
    }
  }

  setForm(): void {
    this.correctionForm = new FormGroup({
      instructions: new FormGroup({
        correctionInstructions: this.correctionInstructionsControl = new FormControl({
          value: this.evaluation.correction_instructions,
          disabled: this.evaluation.active_correction
        }),
        datesCorrection: this.datesCorrectionGroup = new FormGroup({
            dateStartCorrection: this.dateStartCorrectionControl = new FormControl({
              value : this.evaluation.date_start_correction,
              disabled: this.evaluation.active_correction
              },
              //Validators.required
            ),
            dateEndCorrection: this.dateEndCorrectionControl = new FormControl({
              value: this.evaluation.date_end_correction,
              disabled: this.evaluation.active_correction
              },
              //Validators.required
            ),
            timeStartCorrection: this.timeStartCorrectionControl = new FormControl({
              value: this.evaluation.date_start_correction,
              disabled: this.evaluation.active_correction
              },
              //Validators.required
            ),
            timeEndCorrection: this.timeEndCorrectionControl = new FormControl({
              value: this.evaluation.date_end_correction,
              disabled: this.evaluation.active_correction
              },
              //Validators.required
            )
          },
          /*Validators.compose([
            DateRangeValidator(),
            DateMinValidator(new FormControl(this.evaluation.date_start_assignment),
              new FormControl(this.evaluation.date_start_assignment), 'Start')
          ])*/
        )
      }),
      correctionMode: new FormGroup({
        individualCorrection: this.individualCorrectionControl = new FormControl({
          value: this.evaluation.individual_correction,
          disabled: this.evaluation.active_correction,
          },
          //Validators.required
        ),
        anonymity: this.anonymityControl = new FormControl({
          value: this.evaluation.anonymity,
          disabled: this.evaluation.active_correction,
          },
          //Validators.required
        ),
        numberCorrections: this.numberCorrectionsControl = new FormControl({
          value: this.evaluation.number_corrections,
          disabled: this.evaluation.active_correction,
          },
          /*Validators.compose([
            Validators.required,
            Validators.min(1),
            Validators.max(this.evaluation.individual_assignment ? this.evaluation.users.length - 1 : this.evaluation.groups.length - 1 ),
            StepValidator(1)
          ])*/
        ),
      }),
      opinion: new FormGroup({
        datesOpinion: this.datesOpinionGroup = new FormGroup({
            dateEndOpinion: this.dateEndOpinionControl = new FormControl({
                value: this.evaluation.date_end_opinion,
                disabled: this.evaluation.active_correction
              },
              //Validators.required
            ),
            timeEndOpinion: this.timeEndOpinionControl = new FormControl({
                value: this.evaluation.date_end_opinion,
                disabled: this.evaluation.active_correction
              },
              //Validators.required
            ),
          },
          //DateMinValidator(this.dateEndCorrectionControl, this.timeEndCorrectionControl, 'End')
        )
      }),
      criterias: this.criteriasControl = new FormControl(
        this.evaluation.id ? this.evaluation.getCriteriasId() : this.getNullCriteriasId()
      )
    });
  }
  getNullCriteriasId() {
    const ids = [];
    this.evaluation.sections.forEach(section => {
      ids.push([]);
    });
    return ids;
  }

  // Criterias
  moveCriteria(section: Section, criteriaToMove: Criteria, move: number): void {
    this.criteriaUpdated = true;
    section.criterias.find(criteria => criteria.order === criteriaToMove.order + move).order -= move;
    criteriaToMove.order += move;
    this.filterService.sortList(section.criterias, 'order', true);
    this.criteriasControl.markAsDirty();
    this.checksFront();
  }
  deleteCriteria(section: Section, criteriaToDelete: Criteria): void {
    let index: number;
    let order: number;

    this.criteriaUpdated = true;
    index = section.criterias.indexOf(criteriaToDelete);
    order = section.criterias[index].order;
    section.criterias.splice(index, 1);
    section.criterias.forEach(criteria => {
      if (criteria.order >= order) {
        criteria.order--;
      }
    });
    // CriteriasControl
    const criteriasId = this.evaluation.getCriteriasId();
    criteriasId[index] = [];
    this.evaluation.sections[index].criterias.forEach(criteria => {
      criteriasId[index].push(criteria.id);
    });
    this.criteriasControl.setValue(criteriasId);
    this.criteriasControl.markAsDirty();
    this.checksFront();
  }

  // VIEWS
  displayAttributions() {
    this.view_attributions = true;
  }
  displayCriteriaForm(section, criteria?: Criteria) {
    if (!isUndefined(criteria) && criteria.id == null) {
      criteria.id = 0;
    }
    criteria = ( isUndefined(criteria) ? new Criteria() : criteria );
    this.section_tmp = section;
    this.criteria_tmp = criteria;
    this.view_criteria_form = true;
    this.criteriasControl.markAsTouched();
    this.criteriasControl.markAsDirty();
  }

  // CORRECTION
  cancel(): void {
    let status: string;

    status = this.evaluationService.getEvaluationStatus(this.evaluation);
    this.router.navigate(['/teacher', 'evaluations', status]);
  }
  saveCorrection(): void {
    if (this.formService.checkEmptyChecks(this.saveChecks)) {
      this.loaderService.display(true);
      this.applyFormData();
      this.restService.updateDb('evaluationCorrection', this._evaluation)
        .then(response => {
          if (response.success) {
            // EVAL
            this.getEntity(response.evaluation);
          } else {
            this.checksBack(false, response.errors);
            this.alertService.configWaitingAlert('Impossible d\'enregistrer. Vérifier les champs en rouge.', 'error');
          }
          return response;
        })
        .then(response => {
          if (response.success) {
            this.checksBack();
            // ALERT
            this.alertService.configWaitingAlert('La correction a bien été enregistrée.');
          }
          // CHECKS
          this.criteriaUpdated = false;
          this.correctionForm.markAsPristine();
          this.correctionForm.markAsUntouched();
          if (!response.success) {
            this.correctionInstructionsControl.markAsDirty();
            console.log(this.correctionForm);
          }
          this.checksFront();
          // LOADER
          this.loaderService.display(false);
        })
        .catch(() => {
          console.error(`Correction \'${this.evaluation.name}\' cannot be changed in the database.`);
          this.alertService.configWaitingAlert('Une erreur est survenue...', 'error');
          this.loaderService.display(false);
        });
    }
  }
  // TOGGLE
  toggleCorrection(display_alert: boolean = true) {
    if (this.formService.checkEmptyChecks(this.activateChecks)) {
      this.loaderService.display(true);
      this.restService.updateDb('correction_toggle', this.evaluation)
        .then(response => {
          if (response.success) {
            this.correctionForm.markAsPristine();
            if (response.warning && !confirm('Attention, des étudiants ont déjà effectué leurs corrections. Si vous effectuez des modifications vous risquez de supprimer leur travail! Êtes-vous sur de continuer ?')) {
              response.success = false;
              this.toggleCorrection(false);
            } else {
              this.getEntity(response.evaluation);
            }
          } else {
            this.checksBack(false, response.errors);
            this.alertService.configWaitingAlert('Impossible d' + (this.evaluation.active_correction ? 'e dés' : '\'') + 'activer. Vérifier les champs en rouge.', 'error');
          }
          return response;
        })
        .then(response => {
          if (response.success) {
            // FORM
            this.evaluation.active_correction ? this.correctionForm.disable() : this.correctionForm.enable();
            // WYSIWYG
            this.ngOnDestroy();
            this.ngAfterViewInit();
            // CHECKS
            this.criteriaUpdated = false;
            this.correctionForm.markAsPristine();
            this.correctionForm.markAsUntouched();
            this.checksFront(true);
            // ALERT
            display_alert ? this.alertService.configWaitingAlert('La correction a bien été ' + (response.evaluation.active_correction ? '' : 'dés') + 'activée.') : null;
          }
          this.loaderService.display(false);
        });
    }
  }

  resetCorrectionsAttributions() {
    this.loaderService.display(true);
    this.restService.updateDb('correctionAttributionsReset', this.evaluation)
      .then(attributions => {
        if (attributions.success) {
          this.getAttributions(attributions);
          this.alertService.configWaitingAlert('La répartition a bien été générée.');
        } else {
          this.alertService.configWaitingAlert('Une erreur est survenue lors de la génération des attributions.', 'error');
        }
        this.loaderService.display(false);
      });
  }

  //
  getEntity(entity) {
    this.evaluation = new Evaluation(entity);
    this.filterService.sortList(this.evaluation.sections, 'order', true);
    this.evaluation.sections.forEach(section => this.filterService.sortList(section.criterias, 'order', true));
    this.getAttributions();
  }
  applyFormData(): void {
    this._evaluation = Object.assign({}, this.evaluation);
    // INSTRUCTIONS
    this._evaluation.correction_instructions = this.correctionInstructionsControl.value;
    if (this.dateStartCorrectionControl.value !== null) {
      this._evaluation.date_start_correction = joinDateAndTime(this.dateStartCorrectionControl.value, this.timeStartCorrectionControl.value);
    } else if (this._evaluation.date_start_assignment !== null) {
      this._evaluation.date_start_correction = null;
    }
    if (this.dateEndCorrectionControl.value !== null) {
      this._evaluation.date_end_correction = joinDateAndTime(this.dateEndCorrectionControl.value, this.timeEndCorrectionControl.value);
    } else if (this._evaluation.date_end_correction !== null) {
      this._evaluation.date_end_correction = null;
    }
    // PARAMETERS
    this._evaluation.individual_correction = this.individualCorrectionControl.value;
    this._evaluation.anonymity = this.anonymityControl.value;
    // ATTRIBUTION
    this._evaluation.number_corrections = this.numberCorrectionsControl.value;
    // OPINION
    if (this.dateEndOpinionControl.value !== null) {
      this._evaluation.date_end_opinion = joinDateAndTime(this.dateEndOpinionControl.value, this.timeEndOpinionControl.value);
    } else if (this._evaluation.date_end_opinion !== null) {
      this._evaluation.date_end_opinion = null;
    }
    // CRITERIAS
    this._evaluation.sections = [];
    this.evaluation.sections.forEach((section, index_section) => {
      this._evaluation.sections.push(Object.assign({}, section));
      this._evaluation.sections[index_section].criterias = [];
      section.criterias.forEach((criteria, index_criteria) => {
        this._evaluation.sections[index_section].criterias.push(Object.assign({}, criteria));
        if (this._evaluation.sections[index_section].criterias[index_criteria].criteria_type.id !== 2) {
          this._evaluation.sections[index_section].criterias[index_criteria].criteria_choices = null;
        }
        this._evaluation.sections[index_section].criterias[index_criteria].criteria_type = this._evaluation.sections[index_section].criterias[index_criteria].criteria_type.id;
      });
    });
  }

}

import { AfterViewChecked, AfterViewInit, Component, Input, OnDestroy, OnInit } from '@angular/core';
import { FormGroup, FormArray, FormControl, Validators } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { isUndefined } from 'ngx-bootstrap/bs-moment/utils/type-checks';

// Environment
import { environment } from '../../../../environments/environment';

// Classes
import { Correction, CorrectionCriteria, CorrectionSection, SummaryAssets, Opinion } from '../../class/app.evaluation.class';

// Animations
import { routerAnimation } from '../../../../animations/router.animations';

// Services
import { AppCoreRestService } from '../../../core/service/app.core.rest.service';
import { AppAssignmentService } from '../service/app.assignment.service';
import { AppCoreAlertService } from '../../../core/service/app.core.alert.service';
import { AppCoreFormService } from '../../../core/service/app.core.form.service';
import { AppAuthAuthenticationService } from '../../../auth/service/app.auth.authentication.service';
import { AppCoreLoaderService } from '../../../core/service/app.core.loader.service';
import { AppCoreFilterService } from '../../../core/service/app.core.filter.service';

// Validators
import {StepValidator} from '../../../core/validator/app.core.step.validator';

// -----

@Component ({
  selector: 'app-assignment-correction-form',
  templateUrl: '../html/app.assignment.correction.form.component.html',
  styleUrls: ['../../../../assets/css/app.assignment.correction.form.component.scss'], // Change 'scss' to 'css'
  animations: [routerAnimation],
  host: { '[@routerAnimation]': '' }
})
export class AppAssignmentCorrectionFormComponent implements OnInit, OnDestroy, AfterViewInit, AfterViewChecked {

  // ENV
  environment = environment;
  @Input() correctionId: number;
  correction: Correction;
  _correction: any;
  correctionAssets: SummaryAssets;
  opinionAssets: SummaryAssets;
  // FORM
  correctionForm: FormGroup;
  criteriasGroups: FormGroup[][];
  // CONTROLS
  // VIEWS
  view_instructions: Boolean = false;
  view_opinion: Boolean = false;
  opinion_tmp: Opinion;
  // WYSIWYG
  editors = {};

  constructor(
    private route: ActivatedRoute,
    private router: Router,
    private restService: AppCoreRestService,
    private authService: AppAuthAuthenticationService,
    private loaderService: AppCoreLoaderService,
    private formService: AppCoreFormService,
    private alertService: AppCoreAlertService,
    private filterService: AppCoreFilterService,
    private assignmentService: AppAssignmentService
  ) {
    console.log('__CONSTRUCT__ app.assignment.correction.form.component');
    this.authService.checkRole(['student', 'teacher'], true);
  }

  ngOnInit(): void {
    this.opinion_tmp = null;
    // GET
    this.getCorrection();
  }
  canDeactivate() {
    if (this.correctionForm && this.correctionForm.dirty) {
      return confirm('Êtes-vous sûr de quitter le formulaire sans enregistrer vos modifications?');
    }
    return true;
  }

  // WYSIWYG
  ngAfterViewInit() {
    if (this.correction && this.correctionForm && Object.keys(this.editors).length === 0) {
      this.correction.correction_sections.forEach(correction_section => {
        correction_section.correction_criterias.forEach(correction_criteria => {
          if (correction_criteria.criteria.criteria_type.id === 1 || correction_criteria.criteria.criteria_type.id === 3) {
            this.formService.wysiwyg(this.editors, 'comments-' + this.correction.id + '-' + (correction_section.assignment_section.section.order - 1) + '-' + (correction_criteria.criteria.order - 1), this.criteriasGroups[correction_section.assignment_section.section.order - 1][correction_criteria.criteria.order - 1].controls.comments, !this.isEditable());
          }
        });
      });
    }
  }
  ngAfterViewChecked() {
    this.ngAfterViewInit();
  }
  ngOnDestroy() {
    this.editors = this.formService.wysiwyg_remove(this.editors);
  }

  // GET
  getCorrection(): void {
    let id: number;
    let urlKey: string;

    id = ( isUndefined(this.correctionId) ? +this.route.snapshot.params['id'] : this.correctionId );
    urlKey = ( isUndefined(this.correctionId) ? 'corrections' : 'correctionsOpinions' );
    this.loaderService.display(true);
    this.restService.getDb(urlKey, [id])
      .then(correction => this.getEntity(correction))
      .then(() => {
        this.filterService.sortList(this.correction.correction_sections, 'assignment_section.section.order', true);
        this.correction.correction_sections.forEach(correctionSection => {
          this.filterService.sortList(correctionSection.correction_criterias, 'criteria.order', true);
        });
        this.correctionAssets = this.assignmentService.defineCorrectionAssets(this.correction);
        this.opinionAssets = this.assignmentService.defineOpinionAssets(this.correction.assignment);
        this.setForm();
        this.loaderService.display(false);
      });
  }

  // FORM
  setForm(): void {
    this.correctionForm = new FormGroup({
      sections: new FormArray(this.createSectionsArray())
    });
  }
  createSectionsArray(): FormArray[] {
    let sectionsArray: FormArray[];

    sectionsArray = [];
    this.criteriasGroups = [];
    this.correction.correction_sections.forEach(correctionSection => {
      this.criteriasGroups[correctionSection.assignment_section.section.order - 1] = [];
      sectionsArray[correctionSection.assignment_section.section.order - 1] = new FormArray(this.createCriteriaGroups(correctionSection));
    });
    return sectionsArray;
  }
  createCriteriaGroups(correctionSection: CorrectionSection): FormGroup[] {
    correctionSection.correction_criterias.forEach(correctionCriteria => {
      this.criteriasGroups[correctionSection.assignment_section.section.order - 1][correctionCriteria.criteria.order - 1] = this.createCriteriaControls(correctionCriteria);
    });
    return this.criteriasGroups[correctionSection.assignment_section.section.order - 1];
  }
  createCriteriaControls(correctionCriteria: CorrectionCriteria): FormGroup {
    if (correctionCriteria.criteria.criteria_type.id === 1) {
      return new FormGroup({
        comments: new FormControl({
            value: correctionCriteria.comments,
            disabled: !this.isEditable()
          },
          [
            Validators.required,
          ]
        )
      });
    } else if (correctionCriteria.criteria.criteria_type.id === 2) {
      return new FormGroup({
        mark: new FormControl({
            value: correctionCriteria.mark,
            disabled: !this.isEditable()
          },
          [
            Validators.required,
          ]
        )
      });
    } else if (correctionCriteria.criteria.criteria_type.id === 3) {
      return new FormGroup({
        mark: new FormControl({
            value: correctionCriteria.mark,
            disabled: !this.isEditable()
          },
          [
            Validators.required,
            Validators.min(correctionCriteria.criteria.mark_min),
            Validators.max(correctionCriteria.criteria.mark_max),
            StepValidator(correctionCriteria.criteria.precision)
          ]
        ),
        comments: new FormControl({
          value: correctionCriteria.comments,
          disabled: !this.isEditable()
        })
      });
    }
  }

  cancel(): void {
    if (this.authService.user.role.id === 2) {
      this.router.navigate(['/student/corrections', this.correctionAssets.finished ? 'finished' : 'in-progress']);
    } else
    if (this.authService.user.role.id === 3) {
      this.router.navigate(['/teacher/statistics', this.correction.assignment.evaluation.id, 'global']);
    }
  }

  isEditable(): boolean {
    return !((this.correctionAssets.finished && this.authService.user.role.id !== 3)
      || (this.correction.user.id !== null && this.authService.user.id !== this.correction.user.id));
  }

  saveCorrection(draft: boolean = true): void {
    this.loaderService.display(true);
    this.applyFormData(draft);
    this.restService.updateDb('correction', this._correction)
      .then(response => {
        if (response.success) {
          this.correctionForm.markAsPristine();
          // EVAL
          this.getEntity(response.correction);
          this.alertService.configWaitingAlert('La correction a bien été ' + (this.correction.draft ?  'enregistrée' :  'envoyée') + '.');
        }
        this.loaderService.display(false);
      })
      .catch(() => {
        console.error(`Correction \'${this.correction.assignment.evaluation.name}\' cannot be changed in the database.`);
        this.alertService.configWaitingAlert('Une erreur est survenue...', 'error');
        this.loaderService.display(false);
      });
  }

  // VIEWS
  displayInstructions() {
    this.view_instructions = true;
  }
  displayOpinion(correctionCriteria: CorrectionCriteria, opinion = false) {
    this.opinion_tmp = new Opinion(Object.assign({}, correctionCriteria.correction_opinion));
    this.opinion_tmp.opinion = opinion ? 1 : -1;
    this.view_opinion = true;
  }

  //
  getEntity(entity) {
    this.ngOnDestroy();
    this.correction = new Correction(entity);
    this.filterService.sortList(this.correction.correction_sections, 'assignment_section.section.order', true);
    this.correction.correction_sections.forEach(correctionSection => {
      this.filterService.sortList(correctionSection.correction_criterias, 'criteria.order', true);
    });
  }
  applyFormData(draft): void {
    let correctionSectionToApply: CorrectionSection;
    let correctionCriteriaToApply: CorrectionCriteria;

    this._correction = Object.assign({}, this.correction);
    this.correction.correction_sections.forEach(correctionSection => {
      correctionSectionToApply = this.correction.correction_sections.find(correctionSectionToFind => correctionSectionToFind.assignment_section.section.order === correctionSection.assignment_section.section.order);
      correctionSection.correction_criterias.forEach(correctionCriteria => {
        correctionCriteriaToApply = correctionSectionToApply.correction_criterias.find(correctionCriteriaToFind => correctionCriteriaToFind.criteria.order === correctionCriteria.criteria.order);
        if (correctionCriteria.criteria.criteria_type.id === 1) {
          correctionCriteriaToApply.comments = this.criteriasGroups[correctionSection.assignment_section.section.order - 1][correctionCriteria.criteria.order - 1].controls.comments.value;
        } else
        if (correctionCriteria.criteria.criteria_type.id === 2) {
          correctionCriteriaToApply.mark = this.criteriasGroups[correctionSection.assignment_section.section.order - 1][correctionCriteria.criteria.order - 1].controls.mark.value;
        } else
        if (correctionCriteria.criteria.criteria_type.id === 3) {
          correctionCriteriaToApply.mark = this.criteriasGroups[correctionSection.assignment_section.section.order - 1][correctionCriteria.criteria.order - 1].controls.mark.value;
          correctionCriteriaToApply.comments = this.criteriasGroups[correctionSection.assignment_section.section.order - 1][correctionCriteria.criteria.order - 1].controls.comments.value;
        }
      });
    });
    this._correction.draft = draft;
  }

}

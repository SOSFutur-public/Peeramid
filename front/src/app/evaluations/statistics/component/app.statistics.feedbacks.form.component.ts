import { Component, OnInit } from '@angular/core';
import { FormGroup, FormArray, FormControl } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';

// Environment
import { environment } from '../../../../environments/environment';

// Classes
import { Evaluation, Section } from '../../class/app.evaluation.class';

// Animations
import { routerAnimation } from '../../../../animations/router.animations';

// Services
import { AppCoreRestService } from '../../../core/service/app.core.rest.service';
import { AppCoreAlertService } from '../../../core/service/app.core.alert.service';
import { AppAuthAuthenticationService } from '../../../auth/service/app.auth.authentication.service';
import { AppCoreLoaderService } from '../../../core/service/app.core.loader.service';

// -----

@Component ({
  selector: 'app-statistics-feedbacks-form',
  templateUrl: '../html/app.statistics.feedbacks.form.component.html',
  animations: [routerAnimation],
  host: { '[@routerAnimation]': '' }
})
export class AppStatisticsFeedbacksFormComponent implements OnInit {

  environment: any;
  evaluation: Evaluation;
  _evaluation: any;
  feedbacksForm: FormGroup;
  showAssignmentMarkControl: FormControl;
  criteriaGroups: FormGroup[][];

  constructor(
    private route: ActivatedRoute,
    private router: Router,
    private restService: AppCoreRestService,
    private authService: AppAuthAuthenticationService,
    private loaderService: AppCoreLoaderService,
    private alertService: AppCoreAlertService
  ) {
    this.authService.checkRole(['teacher'], true);
  }

  ngOnInit(): void {
    this.environment = environment;
    this.getEvaluation();
  }

  canDeactivate(): boolean {
    if (this.feedbacksForm && this.feedbacksForm.dirty) {
      return confirm('Êtes-vous sûr de quitter le formulaire sans enregistrer vos modifications?');
    }
    return true;
  }

  getEvaluation(): void {
    let id: number;

    id = +this.route.snapshot.params['id'];
    this.loaderService.display(true);
    this.restService.getDb('evaluations', [id])
      .then(evaluation => this.evaluation = new Evaluation(evaluation))
      .then(() => {
        this.setForm();
        this.loaderService.display(false);
      });
  }

  setForm(): void {
    this.feedbacksForm = new FormGroup({
      showAssignmentMark: this.showAssignmentMarkControl = new FormControl({
        value: this.evaluation.show_assignment_mark,
        disabled: this.isFinished()
      }),
      sections: new FormArray(this.createSectionsArray())
    });
  }

  createSectionsArray(): FormArray[] {
    let sectionGroups: FormArray[];

    sectionGroups = [];
    this.criteriaGroups = [];
    this.evaluation.sections.forEach((section, index) => {
      this.criteriaGroups.push([]);
      sectionGroups.push(new FormArray(this.createCriteriaGroups(section, index)));
    });
    return sectionGroups;
  }

  createCriteriaGroups(section: Section, index: number): FormGroup[] {
    section.criterias.forEach(criteria => {
      this.criteriaGroups[index].push(new FormGroup({
          showMark: new FormControl({
            value: criteria.show_mark,
            disabled: this.isFinished()
          }),
          showTeacherComments: new FormControl({
            value: criteria.show_teacher_comments,
            disabled: this.isFinished()
          }),
          showStudentComments: new FormControl({
            value: criteria.show_student_comments,
            disabled: this.isFinished()
          })
        })
      );
    });
    return this.criteriaGroups[index];
  }

  isFinished(): boolean {
    return (this.evaluation.archived);
  }

  cancel(): void {
    this.router.navigate(['/teacher/statistics', this.evaluation.id, 'global']);
  }

  saveFeedbacks(): void {
    this.loaderService.display(true);
    this.applyFormData();
    this.restService.updateDb('evaluationStat', this._evaluation)
      .then(response => {
        if (response.success) {
          this.feedbacksForm.markAsPristine();
          this.alertService.configWaitingAlert('La correction a bien été enregistrée.');
        }
        this.loaderService.display(false);
      })
      .catch(() => {
        console.error(`Correction \'${this.evaluation.name}\' cannot be changed in the database.`);
        this.alertService.configWaitingAlert('Une erreur est survenue...', 'error');
        this.loaderService.display(false);
      });
  }

  applyFormData(): void {
    this._evaluation = Object.assign({}, this.evaluation);
    this._evaluation.show_assignment_mark = this.showAssignmentMarkControl.value;
    this._evaluation.sections.forEach((section, sectionIndex) => {
      section.criterias.forEach((criteria, criteriaIndex) => {
        criteria.show_mark = this.criteriaGroups[sectionIndex][criteriaIndex].controls.showMark.value;
        criteria.show_teacher_comments = this.criteriaGroups[sectionIndex][criteriaIndex].controls.showTeacherComments.value;
        criteria.show_student_mark = this.criteriaGroups[sectionIndex][criteriaIndex].controls.showStudentComments.value;
      });
    });
  }

}

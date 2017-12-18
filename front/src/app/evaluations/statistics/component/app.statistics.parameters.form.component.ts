import { Component, OnInit } from '@angular/core';
import { FormGroup, FormControl, Validators } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';

// Environment
import { environment } from '../../../../environments/environment';

// Classes
import { Evaluation, MarkMode } from '../../class/app.evaluation.class';

// Animations
import { routerAnimation } from '../../../../animations/router.animations';

// Services
import { AppCoreRestService } from '../../../core/service/app.core.rest.service';
import { AppCoreAlertService } from '../../../core/service/app.core.alert.service';
import { AppAuthAuthenticationService } from '../../../auth/service/app.auth.authentication.service';
import { AppCoreLoaderService } from '../../../core/service/app.core.loader.service';

// -----

@Component ({
  selector: 'app-statistics-parameters-form',
  templateUrl: '../html/app.statistics.parameters.form.component.html',
  animations: [routerAnimation],
  host: { '[@routerAnimation]': '' }
})
export class AppStatisticsParametersFormComponent implements OnInit {

  environment = environment;
  evaluation: Evaluation;
  _evaluation: any;
  markModes: MarkMode[];
  markPrecisionModes: MarkMode[];
  markRoundModes: MarkMode[];
  parametersForm: FormGroup;
  invalidForm: boolean;
  markModeControl: FormControl;
  useTeacherMarkControl: FormControl;
  markPrecisionModeControl: FormControl;
  markRoundModeControl: FormControl;

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
    this.getEvaluation();
    this.getMarkModes();
  }

  canDeactivate(): boolean {
    if (this.parametersForm && this.parametersForm.dirty) {
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

  getMarkModes(): void {
    this.loaderService.display(true);
    this.markModes = [];
    this.restService.getDb('markModes')
      .then(markModes => {
        markModes.forEach(markMode => this.markModes.push(new MarkMode(markMode)));
        this.loaderService.display(false);
      });
    this.loaderService.display(true);
    this.markPrecisionModes = [];
    this.restService.getDb('markPrecisionModes')
      .then(markPrecisionModes => {
        markPrecisionModes.forEach(markPrecisionMode => this.markPrecisionModes.push(new MarkMode(markPrecisionMode)));
        this.loaderService.display(false);
      });
    this.loaderService.display(true);
    this.markRoundModes = [];
    this.restService.getDb('markRoundModes')
      .then(markRoundModes => {
        markRoundModes.forEach(markRoundMode => this.markRoundModes.push(new MarkMode(markRoundMode)));
        this.loaderService.display(false);
      });
  }

  setForm(): void {
    this.parametersForm = new FormGroup({
      markMode: this.markModeControl = new FormControl({
          value: this.evaluation.mark_mode.id,
          disabled: this.isFinished()
        },
        // Validators.required
      ),
      useTeacherMark: this.useTeacherMarkControl = new FormControl({
          value: this.evaluation.use_teacher_mark,
          disabled: this.isFinished()
        },
        // Validators.required
      ),
      markPrecisionMode: this.markPrecisionModeControl = new FormControl({
          value: this.evaluation.mark_precision_mode.id,
          disabled: this.isFinished()
        },
        // Validators.required
      ),
      markRoundMode: this.markRoundModeControl = new FormControl({
          value: this.evaluation.mark_round_mode.id,
          disabled: this.isFinished()
        },
        // Validators.required
      )
    });
  }

  isFinished(): boolean {
    return (this.evaluation.archived);
  }

  applyFormData(): void {
    this._evaluation = Object.assign({}, this.evaluation);
    this._evaluation.mark_mode = this.markModeControl.value;
    this._evaluation.use_teacher_mark = this.useTeacherMarkControl.value;
    this._evaluation.mark_precision_mode = this.markPrecisionModeControl.value;
    this._evaluation.mark_round_mode = this.markRoundModeControl.value;
  }

  cancel(): void {
    this.router.navigate(['/teacher/statistics', this.evaluation.id, 'global']);
  }

  saveParameters(): void {
    this.invalidForm = !this.parametersForm.valid;
    if (this.parametersForm.valid) {
      this.loaderService.display(true);
      this.applyFormData();
      this.restService.updateDb('evaluationStat', this._evaluation)
        .then(response => {
          if (response.success) {
            this.parametersForm.markAsPristine();
            this.evaluation = new Evaluation(response.evaluation);
            this.alertService.configWaitingAlert('Les options de notation ont bien été enregistrées.');
          }
          this.loaderService.display(false);
        })
        .catch(() => {
          console.error(`Parameters of evaluation \'${this.evaluation.name}\' cannot be changed in the database.`);
          this.alertService.configWaitingAlert('Une erreur est survenue...', 'error');
          this.loaderService.display(false);
        });
    }
  }

}

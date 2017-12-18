import { AfterViewChecked, AfterViewInit, Component, EventEmitter, Input, OnDestroy, OnInit, Output } from '@angular/core';
import { FormGroup, FormControl } from '@angular/forms';

// Environment
import { environment } from '../../../../environments/environment';

// Classes
import { Correction, Opinion } from '../../class/app.evaluation.class';

// Animations
import { slideInOutAnimation } from '../../../../animations/slide.animations';

// Services
import { AppCoreRestService } from '../../../core/service/app.core.rest.service';
import { AppAuthAuthenticationService } from '../../../auth/service/app.auth.authentication.service';
import { AppCoreAlertService } from '../../../core/service/app.core.alert.service';
import { AppCoreFormService } from '../../../core/service/app.core.form.service';
import { AppCoreLoaderService } from '../../../core/service/app.core.loader.service';

// -----

@Component ({
  selector: 'app-correction-opinion',
  templateUrl: '../html/app.assignment.correction.opinion.form.component.html',
  styleUrls: ['../../../../assets/css/app.assignment.correction.opinion.form.component.scss'], // change 'scss' to 'css'
  animations: [slideInOutAnimation],
  host: { '[@slideInOutAnimation]': '' }
})
export class AppAssignmentCorrectionOpinionFormComponent implements OnInit, OnDestroy, AfterViewInit, AfterViewChecked {

  @Input() finished: boolean;
  @Input() correction: Correction;
  @Input() opinion: Opinion;
  @Input() getViewOpinion: Boolean;
  @Output() correctionChange = new EventEmitter();
  @Output() getViewOpinionChange = new EventEmitter();
  _opinion: any;
  // ENV
  environment = environment;
  // FORM
  opinionForm: FormGroup;
  opinionControl: FormControl;
  commentsControl: FormControl;
  // WYSIWYG
  editors = {};

  constructor(
    private restService: AppCoreRestService,
    private authService: AppAuthAuthenticationService,
    private loaderService: AppCoreLoaderService,
    private formService: AppCoreFormService,
    private alertService: AppCoreAlertService,
  ) {
    console.log('__CONSTRUCT__ app.assignment.correction.opinion.component');
    this.authService.checkRole(['student'], true);
  }

  ngOnInit(): void {
    // FORM
    this.setForm();
    console.log(this.opinion);
    console.log(this.correction);
  }
  ngAfterViewInit() {
    if (this.correction && this.opinion && this.opinionForm && Object.keys(this.editors).length === 0) {
      this.formService.wysiwyg(this.editors, 'comments', this.commentsControl, this.finished);
    }
  }
  ngAfterViewChecked() {
    this.ngAfterViewInit();
  }
  ngOnDestroy() {
    this.formService.wysiwyg_remove(this.editors);
  }

  // FORM
  setForm(): void {
    this.opinionForm = new FormGroup({
      opinion: this.opinionControl = new FormControl({
        value: this.opinion.opinion,
        disabled: this.finished
      }
      ),
      comments: this.commentsControl = new FormControl({
        value: this.opinion.comments,
        disabled: this.finished
      }
      )
    });
  }

  setOpinion(opinion: number): void {
    if (!this.finished) {
      this.opinionControl.setValue(this.opinionControl.value !== opinion ? opinion : 0);
    }
  }

  cancel(): void {
    this.getViewOpinion = false;
    this.getViewOpinionChange.emit(false);
  }

  saveOpinion(): void {
    this.loaderService.display(true);
    this.applyFormData();
    this.restService.updateDb('correctionOpinion', this._opinion)
      .then(response => {
        if (response.success) {
          this.getEntity(response.correction_opinion);
        }
        return response;
      })
      .then(response => {
        if (response.success) {
          this.correction.correction_sections.forEach((correction_section, index_correction_section) => {
            correction_section.correction_criterias.forEach((correction_criteria, index_correction_criteria) => {
              if (correction_criteria.correction_opinion.id === this.opinion.id) {
                this.correction.correction_sections[index_correction_section].correction_criterias[index_correction_criteria].correction_opinion = this.opinion;
              }
            });
          });
        }
        return response;
      })
      .then(response => {
        if (response.success) {
          this.alertService.configWaitingAlert('L\'opinion a bien été enregistrée');
        } else {
          this.alertService.configWaitingAlert('Une erreur est survenue...', 'error');
        }
        this.correctionChange.emit(this.correction);
        this.cancel();
        this.loaderService.display(false);
      })
      .catch(() => {
        console.error(`Opinion cannot be changed in the database.`);
        this.alertService.configWaitingAlert('Une erreur est survenue...', 'error');
        this.loaderService.display(false);
      });
  }

  //
  getEntity(entity) {
    this.opinion = new Opinion(entity);
  }
  applyFormData(): void {
    this._opinion = Object.assign({}, this.opinion);
    this._opinion.opinion = this.opinionControl.value;
    this._opinion.comments = this.commentsControl.value;
    console.log(this._opinion);
  }

}

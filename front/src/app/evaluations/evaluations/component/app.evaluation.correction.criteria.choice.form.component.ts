import {Component, EventEmitter, Input, OnInit, Output} from '@angular/core';
import { FormGroup, FormControl, Validators } from '@angular/forms';

// Classes
import { Criteria, CriteriaChoice } from '../../class/app.evaluation.class';

// Animations
import { routerAnimation } from '../../../../animations/router.animations';

// Services
import { AppAuthAuthenticationService } from '../../../auth/service/app.auth.authentication.service';
import { AppCoreFormService } from '../../../core/service/app.core.form.service';

// -----

@Component ({
  selector: 'app-evaluation-correction-criteria-choice-form',
  templateUrl: '../html/app.evaluation.correction.criteria.choice.form.component.html',
  animations: [routerAnimation],
  host: { '[@routerAnimation]': '' }
})
export class AppEvaluationCorrectionCriteriaChoiceFormComponent implements OnInit {

  @Input() criteria: Criteria;
  @Input() criteriaChoicesControl;
  @Output() criteriaChoicesControlChange = new EventEmitter();
  choiceForm: FormGroup;
  choiceTmp: CriteriaChoice;
  displayChoiceForm: boolean = false;
  choiceIsNew: boolean;
  choiceNameControl: FormControl;
  choiceMarkControl: FormControl;
  // ERRORS
  backChecks = null;
  saveChecks = null;
  backControls = {};
  saveControls = {};

  constructor(
    private authService: AppAuthAuthenticationService,
    private formService: AppCoreFormService,
  ) {
    this.authService.checkRole(['teacher'], true);
  }

  ngOnInit(): void {
    this.choiceTmp = null;
    this.choiceIsNew = false;
    this.setForm();
    // CHECKS
    this.checks();
    this.checksFront(true);
    this.checksBack(true);
  }
  // CHECKS
  checks() {
    this.choiceForm.valueChanges.subscribe(data => { this.checksFront(); });
  }
  checksFront(init: boolean = false) {
    // Function
    if (init) {
      this.saveControls = {
        'choiceName':     [this.choiceNameControl,    {
          'required' : 'Le nom du choix est requis.'
        }, 'Nom', null],
        'choiceMark':     [this.choiceMarkControl,    {
          'required' : 'La note du choix est requise.'
        }, 'Note', null],
      };
    }
    this.saveChecks = this.formService.checkFrontErrors(this.saveControls);
  }
  checksBack(init: boolean = false, response = []) {
    if (init) {
      this.backControls = [
        'choiceName',
        'choiceMark',
      ];
    }
    this.backChecks = this.formService.checkBackErrors(this.backControls, response);
  }

  setForm(): void {
    this.choiceForm = new FormGroup({
      name: this.choiceNameControl = new FormControl(
        '',
        //Validators.required
      ),
      mark: this.choiceMarkControl = new FormControl(
        null,
        /*Validators.compose([
          Validators.required
        ])*/
      )
    });
  }

  updateChoice(choice: CriteriaChoice): void {
    this.choiceTmp = choice;
    this.choiceNameControl.setValue(choice.name);
    this.choiceMarkControl.setValue(choice.mark);
  }

  createChoice(): void {
    this.choiceTmp = new CriteriaChoice();
    this.criteria.criteria_choices.push(this.choiceTmp);
    this.choiceIsNew = true;
    this.displayChoiceForm = true;
  }

  deleteChoice(choiceToDelete: CriteriaChoice): void {
    let index: number;

    index = this.criteria.criteria_choices.indexOf(choiceToDelete);
    this.criteria.criteria_choices.splice(index, 1);
    this.criteriaChoicesControl.setValue(this.criteria.getChoicesId());
    this.criteriaChoicesControlChange.emit(this.criteriaChoicesControl);
  }

  closeChoiceUpdate(): void {
    let index: number;

    this.choiceForm.reset();
    if (this.choiceIsNew) {
      index = this.criteria.criteria_choices.findIndex(choice => choice === this.choiceTmp);
      this.criteria.criteria_choices.splice(index, 1);
    }
    this.choiceTmp = null;
    this.choiceIsNew = false;
    this.displayChoiceForm = false;
    this.choiceForm.markAsPristine();
    this.choiceForm.markAsUntouched();
    this.checksFront();
  }

  saveChoice(): void {
    let index: number;

    index = this.criteria.criteria_choices.findIndex(choice => choice === this.choiceTmp);
    if (this.formService.checkEmptyChecks(this.saveChecks)) {
      this.criteria.criteria_choices[index].name = this.choiceNameControl.value;
      this.criteria.criteria_choices[index].mark = this.choiceMarkControl.value;
      this.choiceIsNew = false;
      this.displayChoiceForm = false;
      this.criteriaChoicesControl.setValue(this.criteria.getChoicesId());
      this.criteriaChoicesControlChange.emit(this.criteriaChoicesControl);
      this.closeChoiceUpdate();
    }
  }

}

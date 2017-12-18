import { Component, OnInit } from '@angular/core';
import { FormControl, Validators, FormArray} from '@angular/forms';
import { Router } from '@angular/router';

// Classes
import { Setting } from '../class/app.setting.class';

// Animations
import { routerAnimation } from '../../../animations/router.animations';

// Services
import { AppCoreRestService } from '../../core/service/app.core.rest.service';
import { AppCoreAlertService } from '../../core/service/app.core.alert.service';
import { AppCoreFormService } from '../../core/service/app.core.form.service';
import { AppAuthAuthenticationService } from '../../auth/service/app.auth.authentication.service';
import { AppCoreLoaderService } from '../../core/service/app.core.loader.service';

// Validators
import { StepValidator } from '../../core/validator/app.core.step.validator';

// -----

@Component ({
  selector: 'app-settings-admin',
  templateUrl: '../html/app.settings.form.component.html',
  animations: [routerAnimation],
  host: { '[@routerAnimation]': '' }
})
export class AppSettingsFormComponent implements OnInit {

  settings: Setting[];
  settingsForm: FormArray;
  invalidForm: boolean;
  uploadSizeLimitControl: FormControl;
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
    private alertService: AppCoreAlertService,
    private router: Router
  ) {
    console.log('__CONSTRUCT__ app.settings.admin.component');
    this.authService.checkRole(['admin'], true);
  }

  ngOnInit(): void {
    this.getSettings();
    // CHECKS
    //this.checks();
    //this.checksFront(true);
    //this.checksBack(true);
  }
  // CHECKS
  checks() {
    this.settingsForm.valueChanges.subscribe(data => { this.checksFront(); });
  }
  checksFront(init: boolean = false) {
    // Function
    if (init) {
      this.saveControls = {
        'uploadSizeLimit':     [this.uploadSizeLimitControl,    {
          'required' : 'La taille maximale globale des fichiers est requise.',
          'min:0': 'La taille des fichiers ne peut être inférieure à 0.',
          'max:1000': 'La taille des fichiers ne peut être supérieure à 1000 (1Go).'
        }, 'Taille maximale des fichiers', null],
      };
    }
    this.saveChecks = this.formService.checkFrontErrors(this.saveControls);
  }
  checksBack(init: boolean = false, response = []) {
    if (init) {
      this.backControls = [
        'uploadSizeLimit',
      ];
    }
    this.backChecks = this.formService.checkBackErrors(this.backControls, response);
  }

  canDeactivate(): boolean {
    if (this.settingsForm && this.settingsForm.dirty) {
      return confirm('Êtes-vous sûr de quitter le formulaire sans enregistrer vos modifications?');
    }
    return true;
  }

  setForm(): void {
    this.settingsForm = new FormArray([
      this.uploadSizeLimitControl = new FormControl(
        parseInt(this.settings[0].value, 10),
        Validators.compose([
          Validators.required,
          Validators.min(0),
          StepValidator(1)
        ])
      )
    ]);
  }

  getSettings(): void {
    this.loaderService.display(true);
    this.restService.getDb('settings')
      .then(settings => this.settings = settings)
      .then(() => {
        this.setForm();
        this.loaderService.display(false);
      })
      .then(() => {
        this.checks();
        this.checksFront(true);
        this.checksBack(true);
      });
  }

  saveSettings(): void {
    this.invalidForm = !this.settingsForm.valid;
    if (this.settingsForm.valid) {
      this.loaderService.display(true);
      this.settings[0].value = this.uploadSizeLimitControl.value;
      this.restService.updateDb('settings', this.settings)
        .then(response => {
          if (response.success) {
            this.settingsForm.markAsPristine();
            this.alertService.configWaitingAlert('Les paramètres ont bien été enregistrés.', 'success');
            this.checksBack();
          } else {
            this.checksBack(false, response.errors);
            this.alertService.configWaitingAlert('Impossible d\'enregistrer. Vérifier les champs en rouge.', 'error');
          }
          // CHECKS
          this.settingsForm.markAsPristine();
          this.settingsForm.markAsUntouched();
          this.checksFront();
          // LOADER
          this.loaderService.display(false);
        })
        .catch(() => {
          console.error('Settings cannot be changed in the database.');
          this.alertService.configWaitingAlert('Une erreur est survenue...', 'error');
          this.loaderService.display(false);
        });
    }
  }

}

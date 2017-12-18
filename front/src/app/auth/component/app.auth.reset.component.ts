import { Component, OnInit} from '@angular/core';
import { FormBuilder, FormGroup, Validators, FormControl } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';

// Animations
import { routerAnimation } from '../../../animations/router.animations';

// Services
import { AppAuthAuthenticationService } from '../service/app.auth.authentication.service';
import { AppCoreAlertService } from '../../core/service/app.core.alert.service';
import { AppCoreLoaderService } from '../../core/service/app.core.loader.service';

// Validators
import { MatchingPasswordsValidator, StrongPasswordValidator } from '../../core/validator/app.core.matching.passwords.validator';

// -----

@Component({
  selector: 'app-auth-authentication-reset',
  templateUrl: '../html/app.auth.reset.component.html',
  styleUrls: [
    '../../../assets/css/app.auth.authentication.component.scss',
    '../../../assets/css/app.core.form.component.scss'
  ],
  animations: [routerAnimation],
  host: {'[@routerAnimation]': ''}
})
export class AppAuthResetComponent implements OnInit {

  response = {
    token: null,
    password: null
  };
  // FORM
  loginForm: FormGroup;
  invalidForm: boolean;
  // CONTROLS
  passwordControl: FormControl;
  passwordConfirmControl: FormControl;
  // ERRORS
  error: String = null;

  constructor(
    private formBuilder: FormBuilder,
    public authService: AppAuthAuthenticationService,
    private loaderService: AppCoreLoaderService,
    private alertService: AppCoreAlertService,
    private route: ActivatedRoute,
    private router: Router
  ) {
    console.log('__CONSTRUCT__ app.auth.reset.component');
  }

  ngOnInit() {
    this.invalidForm = false;
    this.setForm();
  }

  setForm(): void {
    this.loginForm = this.formBuilder.group({
      passwordGroup: this.formBuilder.group({
          password: this.passwordControl = new FormControl(
            '',
            Validators.compose([Validators.required, StrongPasswordValidator()])
          ),
          passwordConfirm: this.passwordConfirmControl = new FormControl(
            '',
            Validators.compose([Validators.required])
          )
        },
        {
          validator: MatchingPasswordsValidator()
        }),
    });
  }

  cancel() {
    this.router.navigate(['/login']);
  }

  onSubmit() {
    this.error = null;
    this.invalidForm = !this.loginForm.valid;
    if (this.loginForm.valid) {
      this.loaderService.display(true);
      this.applyFormData();
      this.authService.post('reset', this.response)
        .then(response => {
          if (!response.success) {
            this.error = response.errors.message;
          }
        })
        .then(() => {
          if (this.error === null) {
            this.alertService.setAlert('Votre mot de passe a bien été réinitialisé.', 'success');
            this.router.navigate(['/login']);
          }
          this.loaderService.display(false);
        })
        .catch(() => {
          alert(`Une erreur est survenue durant la réinitialisation de votre mot de passe. Veuillez réessayer. Si le problème persiste, contactez votre administrateur système.`);
          this.loaderService.display(false);
        });
    }
  }

  applyFormData(): void {
    this.response.token = this.route.snapshot.params['token'];
    this.response.password = this.passwordControl.value;
  }

}

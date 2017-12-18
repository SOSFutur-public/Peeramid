import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { Router } from '@angular/router';

// Animations
import { routerAnimation } from '../../../animations/router.animations';

// Services
import { AppAuthAuthenticationService } from '../service/app.auth.authentication.service';
import { AppCoreAlertService } from '../../core/service/app.core.alert.service';
import { AppCoreLoaderService } from '../../core/service/app.core.loader.service';

// -----

@Component({
  selector: 'app-auth-authentication-request',
  templateUrl: '../html/app.auth.request.component.html',
  styleUrls: ['../../../assets/css/app.auth.authentication.component.scss'],
  animations: [routerAnimation],
  host: {'[@routerAnimation]': ''}
})
export class AppAuthRequestComponent implements OnInit {

  loginForm: FormGroup;
  error: String = null;

  constructor(
    private formBuilder: FormBuilder,
    public authService: AppAuthAuthenticationService,
    private loaderService: AppCoreLoaderService,
    private alertService: AppCoreAlertService,
    private router: Router
  ) {
    console.log('__CONSTRUCT__ app.auth.request.component');
    this.loginForm = formBuilder.group({
      'username': ['', Validators.required]
    });
  }

  ngOnInit() {}

  cancel() {
    this.router.navigate(['/login']);
  }

  onSubmit() {
    this.loaderService.display(true);
    this.error = null;
    this.authService.post('request', this.loginForm.value)
      .then(data => {
        // console.log(data);
        if (data.success) {
          this.alertService.setAlert('Un email avec un lien pour réinitialiser votre mot de passe vient de vous être envoyé.', 'success');
          this.router.navigate(['/login']);
        } else {
          this.error = data.errors.message;
        }
        this.loaderService.display(false);
      }, () => {
        this.error = 'Une erreur est survenue lors de la réinitialisation du mot de passe. Veuillez réessayer. Si le problème persiste, contactez votre administrateur système.';
        this.loaderService.display(false);
      });
  }

}

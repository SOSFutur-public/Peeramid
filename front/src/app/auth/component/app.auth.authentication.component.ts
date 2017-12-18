import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { Router } from '@angular/router';

// Animations
import { routerAnimation } from '../../../animations/router.animations';

// Services
import { AppAuthAuthenticationService } from '../service/app.auth.authentication.service';
import { AppCoreRestService } from '../../core/service/app.core.rest.service';
import { AppAuthGuardService } from '../service/app.auth.guard.service';
import { AppCoreAlertService } from '../../core/service/app.core.alert.service';
import { AppCoreLoaderService } from '../../core/service/app.core.loader.service';

// -----

@Component({
  selector: 'app-auth-authentication',
  templateUrl: '../html/app.auth.authentication.component.html',
  styleUrls: ['../../../assets/css/app.auth.authentication.component.scss'],
  animations: [routerAnimation],
  host: {'[@routerAnimation]': ''}
})
export class AppAuthAuthenticationComponent implements OnInit {

  loginForm: FormGroup;
  error: String = null;

  constructor(
    private formBuilder: FormBuilder,
    public authService: AppAuthAuthenticationService,
    private loaderService: AppCoreLoaderService,
    private authGuardService: AppAuthGuardService,
    private restService: AppCoreRestService,
    private alertService: AppCoreAlertService,
    private router: Router
  ) {
    console.log('__CONSTRUCT__ app.auth.authentication.component');
    this.loginForm = formBuilder.group({
      'username': ['', Validators.required],
      'password': ['', Validators.required]
    });
  }

  ngOnInit() {
    if (this.authService.loggedIn()) {
      this.router.navigate(['home']);
    }
  }

  onSubmit() {
    this.loaderService.display(true);
    this.error = null;
    this.authService.post('login', this.loginForm.value)
      .then(data => {
        if (data.success) {
          localStorage.setItem('peeramid_token', data.token);
          this.authService.getLoggedUser()
            .then( () => {
              this.alertService.setAlert('Vous êtes connecté en tant que ' + this.authService.user.name(true) + '.', 'success');
              this.router.navigate(['home']);
            });
        } else {
          this.error = data.errors.message;
        }
        this.loaderService.display(false);
      }, () => {
        this.error = 'Une erreur est survenue lors de l\'authentication. Veuillez réessayer. Si le problème persiste, contactez votre administrateur système.';
        this.loaderService.display(false);
      });
  }

  toRequest() {
    this.router.navigate(['/login/request']);
  }

}

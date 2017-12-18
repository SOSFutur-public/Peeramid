import { Component } from '@angular/core';
import { Router } from '@angular/router';

// Environment
import { environment } from '../../../environments/environment';

// Services
import { AppAuthAuthenticationService } from '../../auth/service/app.auth.authentication.service';

// -----

@Component({
  selector: 'app-navbar',
  templateUrl: '../html/app.core.navbar.component.html',
  styleUrls: ['../../../assets/css/app.core.navbar.component.scss']
})

export class AppNavbarComponent {

  environment = environment;

  constructor(
    public authService: AppAuthAuthenticationService,
    private router: Router
  ) {
    console.log('__CONSTRUCT__ app.navbar.component');
  }

  logout() {
    this.authService.logout();
    this.router.navigate(['login']);
  }
  toProfile() {
    console.log(this.authService.user.role.getUrl());
    this.router.navigate([this.authService.user.role.getUrl(), 'profile']);
  }
}

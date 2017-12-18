import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';

// Services
import { AppAuthAuthenticationService } from '../../auth/service/app.auth.authentication.service';

// -----

@Component ({
  selector: 'app-home',
  template: '',
})
export class AppHomeComponent implements OnInit {

  constructor(
    private router: Router,
    private authService: AppAuthAuthenticationService
  ) {
    console.log('__CONSTRUCT__ app.home.component');
    if (!this.authService.loggedIn()) {
      this.router.navigate(['/login']);
    }
  }

  ngOnInit() {
    this.router.navigate(['/' + this.authService.user.role.getUrl(), 'home']);
  }

}

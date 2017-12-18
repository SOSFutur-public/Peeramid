import { Component } from '@angular/core';

// Animations
import { routerAnimation } from '../../../animations/router.animations';

// Services
import { AppAuthAuthenticationService } from '../../auth/service/app.auth.authentication.service';

// -----

@Component ({
  selector: 'app-home-admin',
  templateUrl: '../html/app.home.admin.component.html',
  animations: [routerAnimation],
  host: {'[@routerAnimation]': ''}
})
export class AppHomeAdminComponent {

  constructor(
    private authService: AppAuthAuthenticationService
  ) {
    console.log('__CONSTRUCT__ app.home.admin.component');
    this.authService.checkRole(['admin'], true);
  }

}

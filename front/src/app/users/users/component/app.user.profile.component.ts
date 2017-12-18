import { Component } from '@angular/core';

// Classes
import { User } from '../../class/app.user.class';

// Animations
import { routerAnimation } from '../../../../animations/router.animations';

// Services
import { AppAuthAuthenticationService } from '../../../auth/service/app.auth.authentication.service';

// -----

@Component ({
  selector: 'app-profile',
  templateUrl: '../html/app.user.profile.component.html',
  animations: [routerAnimation],
  host: { '[@routerAnimation]': '' }
})
export class AppUserProfileComponent {

  profile: User = this.authService.user;
  dirty: boolean;

  constructor(
    private authService: AppAuthAuthenticationService
  ) {
    console.log('__CONSTRUCT__ app.user.profile.component');
    this.authService.checkRole(['admin', 'student', 'teacher'], true);
    console.log(this.profile);
  }

  canDeactivate(): boolean {
    if (this.dirty) {
      return confirm('Êtes-vous sûr de quitter le formulaire sans enregistrer vos modifications?');
    }
    return true;
  }

}

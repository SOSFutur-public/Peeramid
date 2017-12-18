import {Injectable, state} from '@angular/core';
import {CanActivate, Router, ActivatedRouteSnapshot, RouterStateSnapshot} from '@angular/router';

// Services
import {AppAuthAuthenticationService} from './app.auth.authentication.service';


// -----
@Injectable()
export class AppAuthGuardService implements CanActivate {

  url: String = '';

  constructor(
    private authService: AppAuthAuthenticationService,
    private router: Router
  ) {}

  canActivate(route: ActivatedRouteSnapshot, state: RouterStateSnapshot) {
    if (this.authService.loggedIn()) {
      this.url = state.url;
      return true;
    } else {
      this.router.navigate(['login']);
      return false;
    }
  }

}

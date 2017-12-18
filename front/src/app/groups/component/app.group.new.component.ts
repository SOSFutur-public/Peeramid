import { Component, OnInit } from '@angular/core';

// Classes
import { Group } from '../class/app.group.class';

// Animations
import { routerAnimation } from '../../../animations/router.animations';

// Services
import { AppAuthAuthenticationService } from '../../auth/service/app.auth.authentication.service';

// -----

@Component ({
  selector: 'app-group-new',
  templateUrl: '../html/app.group.new.component.html',
  animations: [routerAnimation],
  host: { '[@routerAnimation]': '' }
})
export class AppGroupNewComponent implements OnInit {

  newGroup: Group;
  dirty: boolean;

  constructor(
    private authService: AppAuthAuthenticationService
  ) {
    console.log('__CONSTRUCT__ app.group.new.component');
    this.authService.checkRole(['admin'], true);
  }

  ngOnInit(): void {
    this.newGroup = new Group;
  }

  canDeactivate(): boolean {
    if (this.dirty) {
      return confirm('Êtes-vous sûr de quitter le formulaire sans enregistrer vos modifications?');
    }
    return true;
  }

}

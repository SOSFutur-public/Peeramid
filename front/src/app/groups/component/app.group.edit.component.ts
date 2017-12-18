import { Component, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';

// Classes
import { Group } from '../class/app.group.class';

// Animations
import { routerAnimation } from '../../../animations/router.animations';

// Services
import { AppCoreRestService } from '../../core/service/app.core.rest.service';
import { AppAuthAuthenticationService } from '../../auth/service/app.auth.authentication.service';
import { AppCoreLoaderService } from '../../core/service/app.core.loader.service';

// -----

@Component ({
  selector: 'app-group-edit',
  templateUrl: '../html/app.group.edit.component.html',
  animations: [routerAnimation],
  host: { '[@routerAnimation]': '' }
})
export class AppGroupEditComponent implements OnInit {

  editGroup: Group;
  dirty: boolean;

  constructor(
    private route: ActivatedRoute,
    private loaderService: AppCoreLoaderService,
    private restService: AppCoreRestService,
    private authService: AppAuthAuthenticationService
  ) {
    console.log('__CONSTRUCT__ app.group.edit.component');
    this.authService.checkRole(['admin'], true);
  }

  ngOnInit(): void {
    let id: number;
    id = +this.route.snapshot.params['id'];
    this.getGroup(id);
  }

  canDeactivate(): boolean {
    if (this.dirty) {
      return confirm('Êtes-vous sûr de quitter le formulaire sans enregistrer vos modifications?');
    }
    return true;
  }

  getGroup(id: number): void {
    this.loaderService.display(true);
    this.restService.getDb('groups', [id])
      .then(group => {
        this.editGroup = new Group(group);
        this.loaderService.display(false);
      });
  }

}

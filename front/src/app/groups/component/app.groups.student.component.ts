import { Component, OnInit } from '@angular/core';

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
  selector: 'app-groups-student',
  templateUrl: '../html/app.groups.student.component.html',
  animations: [routerAnimation],
  host: {'[@routerAnimation]': ''}
})
export class AppGroupsStudentComponent implements OnInit {

  groups: Group[];

  constructor(
    private restService: AppCoreRestService,
    private authService: AppAuthAuthenticationService,
    private loaderService: AppCoreLoaderService
  ) {
    console.log('__CONSTRUCT__ app.group.student.component');
    this.authService.checkRole(['student'], true);
  }

  ngOnInit(): void {
    this.getGroups();
  }

  getGroups(): void {
    this.loaderService.display(true);
    this.groups = [];
    this.restService.getDb('userGroups')
      .then(groups => {
        groups.forEach(group => {
          this.groups.push(new Group(group));
        });
        this.loaderService.display(false);
      });
  }

}

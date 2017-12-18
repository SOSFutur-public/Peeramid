import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';

// Classes
import { Assignment } from '../../class/app.evaluation.class';

// Animations
import { routerAnimation } from '../../../../animations/router.animations';

// Services
import { AppCoreRestService } from '../../../core/service/app.core.rest.service';
import { AppAuthAuthenticationService } from '../../../auth/service/app.auth.authentication.service';
import { AppCoreAlertService } from '../../../core/service/app.core.alert.service';
import { AppCoreLoaderService } from '../../../core/service/app.core.loader.service';

// -----

@Component ({
  selector: 'app-assignments-list',
  templateUrl: '../html/app.assignments.list.component.html',
  animations: [routerAnimation],
  host: { '[@routerAnimation]': '' }
})
export class AppAssignmentsListComponent implements OnInit {

  assignments: { individual_assignments: Assignment[], group_assignments: Assignment[] };
  status: string;

  constructor(
    private router: Router,
    private route: ActivatedRoute,
    private restService: AppCoreRestService,
    private authService: AppAuthAuthenticationService,
    private loaderService: AppCoreLoaderService,
    private alertService: AppCoreAlertService,
  ) {
    console.log('__CONSTRUCT__ app.assignments.list.component');
    this.authService.checkRole(['student'], true);
  }

  ngOnInit(): void {
    this.route.params.subscribe(
      params => {
        this.status = params['status'];
        if (this.getFrenchStatus(this.status) === null) {
          this.alertService.setAlert('Cette page n\'est pas reconnue...', 'warning');
          this.router.navigate(['/home']);
        } else {
          this.getAssignments(this.status);
        }
      });
  }

  getAssignments(status: string): void {
    this.loaderService.display(true);
    this.assignments = {
      individual_assignments: [],
      group_assignments: []
    };
    this.restService.getDb('userAssignments', null, status)
      .then(assignments => {
        assignments.individual_assignments.forEach(individual_assignment => {
          this.assignments.individual_assignments.push(new Assignment(individual_assignment));
        });
        assignments.group_assignments.forEach(group_assignment => {
          this.assignments.group_assignments.push(new Assignment(group_assignment));
        });
        this.loaderService.display(false);
      });
  }

  getFrenchStatus(status: string): string {
    switch (status) {
      case 'in-progress' : {
        return 'EN COURS';
      }
      case 'finished' : {
        return 'TERMINÉS';
      }
      default: {
        return null;
      }
    }
  }

}

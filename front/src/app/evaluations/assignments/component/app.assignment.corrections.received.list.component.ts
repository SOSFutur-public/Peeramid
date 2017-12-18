import { Component, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';

// Classes
import { Assignment } from '../../class/app.evaluation.class';

// Animations
import { routerAnimation } from '../../../../animations/router.animations';

// Services
import { AppCoreRestService } from '../../../core/service/app.core.rest.service';
import { AppAuthAuthenticationService } from '../../../auth/service/app.auth.authentication.service';
import { AppCoreFilterService } from '../../../core/service/app.core.filter.service';
import { AppCoreLoaderService } from '../../../core/service/app.core.loader.service';

// -----

@Component ({
  selector: 'app-assignment-corrections-received-list',
  templateUrl: '../html/app.assignment.corrections.received.list.component.html',
  styleUrls: ['../../../../assets/css/app.assignment.corrections.received.list.component.scss'],
  animations: [routerAnimation],
  host: { '[@routerAnimation]': '' }
})
export class AppAssignmentCorrectionsReceivedListComponent implements OnInit {

  assignment: Assignment;

  constructor(
    private route: ActivatedRoute,
    private authService: AppAuthAuthenticationService,
    private loaderService: AppCoreLoaderService,
    private restService: AppCoreRestService,
    private filterService: AppCoreFilterService
  ) {
    console.log('__CONSTRUCT__ app.corrections.received.list.component');
    this.authService.checkRole(['student'], true);
  }

  ngOnInit(): void {
    this.getAssignment(+this.route.snapshot.params['id']);
  }

  getAssignment(id: number): void {
    this.loaderService.display(true);
    this.restService.getDb('assignmentCorrections', [id])
      .then(assignment => this.assignment = new Assignment(assignment, true))
      .then(() => this.filterService.sortList(this.assignment.corrections, 'user.role.id'))
      .then(() => {
        console.log(this.assignment);
        this.loaderService.display(false);
      });
  }

}

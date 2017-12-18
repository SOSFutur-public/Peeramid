import { Component, OnInit } from '@angular/core';

// Classes
import { Assignment, Correction } from '../../evaluations/class/app.evaluation.class';

// Animations
import { routerAnimation } from '../../../animations/router.animations';

// Services
import { AppCoreRestService } from '../../core/service/app.core.rest.service';
import { AppAuthAuthenticationService } from '../../auth/service/app.auth.authentication.service';
import { AppCoreLoaderService } from '../../core/service/app.core.loader.service';

// -----

@Component ({
  selector: 'app-home-student',
  templateUrl: '../html/app.home.student.component.html',
  animations: [routerAnimation],
  host: {'[@routerAnimation]': ''}
})
export class AppHomeStudentComponent implements OnInit {

  assignments: { individual_assignments: Assignment[], group_assignments: Assignment[] };
  corrections: { individual_corrections: Correction[], group_corrections: Correction[] };

  constructor(
    private loaderService: AppCoreLoaderService,
    private restService: AppCoreRestService,
    private authService: AppAuthAuthenticationService
  ) {
    console.log('__CONSTRUCT__ app.home.student.component');
    this.authService.checkRole(['student'], true);
  }

  ngOnInit(): void {
    this.getAssignments();
    this.getCorrections();
  }

  getAssignments(): void {
    this.loaderService.display(true);
    this.assignments = {
      individual_assignments: [],
      group_assignments: []
    };
    this.restService.getDb('userAssignments', null, 'in-progress')
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

  getCorrections(): void {
    this.loaderService.display(true);
    this.corrections = {
      individual_corrections: [],
      group_corrections: []
    };
    this.restService.getDb('userCorrections', null, 'in-progress')
      .then(corrections => {
        corrections.individual_corrections.forEach(individual_correction => {
          this.corrections.individual_corrections.push(new Correction(individual_correction));
        });
        corrections.group_corrections.forEach(group_correction => {
          this.corrections.group_corrections.push(new Correction(group_correction));
        });
        this.loaderService.display(false);
      });
  }

}

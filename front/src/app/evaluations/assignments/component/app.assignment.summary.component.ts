import { Component, OnInit, Input } from '@angular/core';

// Classes
import { Assignment, SummaryAssets } from '../../class/app.evaluation.class';

// Animations
import { routerAnimation } from '../../../../animations/router.animations';

// Services
import { AppAssignmentService } from '../service/app.assignment.service';
import { AppAuthAuthenticationService } from '../../../auth/service/app.auth.authentication.service';

// -----

@Component ({
  selector: 'app-assignment-summary',
  templateUrl: '../html/app.assignment.summary.component.html',
  animations: [routerAnimation],
  host: { '[@routerAnimation]': '' }
})
export class AppAssignmentSummaryComponent implements OnInit {

  @Input() assignment: Assignment;
  assignmentAssets: SummaryAssets;
  opinionAssets: SummaryAssets;
  correctionFinished: boolean;

  constructor(
    private assignmentService: AppAssignmentService,
    private authService: AppAuthAuthenticationService,
  ) {
    console.log('__CONSTRUCT__ app.assignment.summary.component');
    this.authService.checkRole(['student'], true);
  }

  ngOnInit(): void {
    this.getAssignmentAssets();
    this.getOpinionAssets();
    this.correctionFinished = this.assignment.evaluation.date_end_correction < new Date();
    console.log(this.assignment);
  }

  getAssignmentAssets(): void {
    this.assignmentAssets = this.assignmentService.defineAssignmentAssets(this.assignment);
  }

  getOpinionAssets(): void {
    this.opinionAssets = this.assignmentService.defineOpinionAssets(this.assignment);
  }

}

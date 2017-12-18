import { Component, OnInit, Input } from '@angular/core';

// Classes
import { Correction, SummaryAssets } from '../../class/app.evaluation.class';

// Animations
import { routerAnimation } from '../../../../animations/router.animations';
import { AppAuthAuthenticationService } from '../../../auth/service/app.auth.authentication.service';

// Services
import { AppAssignmentService } from '../service/app.assignment.service';


@Component ({
  selector: 'app-assignment-correction-summary',
  templateUrl: '../html/app.assignment.correction.summary.component.html',
  animations: [routerAnimation],
  host: { '[@routerAnimation]': '' }
})
export class AppAssignmentCorrectionSummaryComponent implements OnInit {

  @Input() correction: Correction;
  assets: SummaryAssets;

  constructor(
    private assignmentService: AppAssignmentService,
    private authService: AppAuthAuthenticationService,
  ) {
    console.log('__CONSTRUCT__ app.correction.summary.component');
    this.authService.checkRole(['student'], true);
  }

  ngOnInit(): void {
    this.getCorrectionAssets();
  }

  getCorrectionAssets(): void {
    this.assets = this.assignmentService.defineCorrectionAssets(this.correction);
  }

}

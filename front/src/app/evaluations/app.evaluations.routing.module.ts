import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';

// Components
import { AppAssignmentsListComponent } from './assignments/component/app.assignments.list.component';
import { AppAssignmentFormComponent } from './assignments/component/app.assignment.form.component';
import { AppAssignmentCorrectionsListComponent } from './assignments/component/app.assignment.corrections.list.component';
import { AppAssignmentCorrectionsReceivedListComponent } from './assignments/component/app.assignment.corrections.received.list.component';
import { AppAssignmentCorrectionFormComponent } from './assignments/component/app.assignment.correction.form.component';
import { AppEvaluationEditComponent } from './evaluations/component/app.evaluation.edit.component';
import { AppEvaluationNewComponent } from './evaluations/component/app.evaluation.new.component';
import { AppEvaluationCorrectionFormComponent} from './evaluations/component/app.evaluation.correction.form.component';
import { AppEvaluationsListComponent } from './evaluations/component/app.evaluations.list.component';
import { AppStatisticsTableComponent } from './statistics/component/app.statistics.table.component';
import { AppStatisticsCalibrationFormComponent } from './statistics/component/app.statistics.calibration.form.component';
import { AppStatisticsReliabilityComponent } from './statistics/component/app.statistics.reliability.component';
import { AppStatisticsParametersFormComponent } from './statistics/component/app.statistics.parameters.form.component';
import { AppStatisticsFeedbacksFormComponent } from './statistics/component/app.statistics.feedbacks.form.component';
import { AppStatisticsChartsComponent } from './statistics/component/app.statistics.charts.component';

// Services
import { AppCoreCanDeactivateGuardService } from '../core/service/app.core.can.deactivate.guard.service';

// -----

const evaluationsRoutes: Routes = [
  {
    path: 'student',
    children: [
      { path: 'assignments/:status', component: AppAssignmentsListComponent },
      { path: 'assignment/:id/edit', component: AppAssignmentFormComponent, canDeactivate: [AppCoreCanDeactivateGuardService] },
      { path: 'assignment/:id/corrections', component: AppAssignmentCorrectionsReceivedListComponent },
      { path: 'corrections/:status', component: AppAssignmentCorrectionsListComponent },
      { path: 'correction/:id/edit', component: AppAssignmentCorrectionFormComponent, canDeactivate: [AppCoreCanDeactivateGuardService] }
    ]
  },
  {
    path: 'teacher',
    children: [
      { path: 'evaluations/:status', component: AppEvaluationsListComponent },
      { path: 'evaluation/:id/edit', component: AppEvaluationEditComponent, canDeactivate: [AppCoreCanDeactivateGuardService] },
      { path: 'evaluation/new', component: AppEvaluationNewComponent, canDeactivate: [AppCoreCanDeactivateGuardService] },
      { path: 'assignment/:id/view-student', component: AppAssignmentFormComponent },
      { path: 'correction/:id',
        children: [
          { path: 'edit', component: AppEvaluationCorrectionFormComponent, canDeactivate: [AppCoreCanDeactivateGuardService] },
          { path: 'mark-student', component: AppAssignmentCorrectionFormComponent, canDeactivate: [AppCoreCanDeactivateGuardService] },
          { path: 'view-student', component: AppAssignmentCorrectionFormComponent}
        ]
      },
      { path: 'statistics/:id',
        children: [
          { path: 'calibration', component: AppStatisticsCalibrationFormComponent, canDeactivate: [AppCoreCanDeactivateGuardService] },
          { path: 'examiners-reliability', component: AppStatisticsReliabilityComponent },
          { path: 'parameters', component: AppStatisticsParametersFormComponent, canDeactivate: [AppCoreCanDeactivateGuardService] },
          { path: 'feedbacks', component: AppStatisticsFeedbacksFormComponent, canDeactivate: [AppCoreCanDeactivateGuardService] },
          { path: 'charts', component: AppStatisticsChartsComponent },
          { path: ':stats-type', component: AppStatisticsTableComponent }
        ]
      }
    ]
  }
];

@NgModule ({
  imports: [
    RouterModule.forChild(evaluationsRoutes)
  ],
  exports: [
    RouterModule
  ]
})
export class AppEvaluationsRoutingModule {}

import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
import { FormsModule } from '@angular/forms';
import { AngularFontAwesomeModule } from 'angular-font-awesome';
import { BsDropdownModule, TabsModule, PopoverModule } from 'ngx-bootstrap';
import { ChartModule } from 'angular2-highcharts';
import { HighchartsStatic } from 'angular2-highcharts/dist/HighchartsService';
import * as Highcharts from 'highcharts';

// Modules
import { AppCoreFormsModule } from '../core/app.core.forms.module';
import { AppEvaluationsRoutingModule } from './app.evaluations.routing.module';

// Components
import { AppAssignmentSummaryComponent } from './assignments/component/app.assignment.summary.component';
import { AppAssignmentsListComponent } from './assignments/component/app.assignments.list.component';
import { AppAssignmentCorrectionSummaryComponent } from './assignments/component/app.assignment.correction.summary.component';
import { AppAssignmentCorrectionsListComponent } from './assignments/component/app.assignment.corrections.list.component';
import { AppAssignmentCorrectionsReceivedListComponent } from './assignments/component/app.assignment.corrections.received.list.component';
import { AppEvaluationEditComponent } from './evaluations/component/app.evaluation.edit.component';
import { AppEvaluationNewComponent } from './evaluations/component/app.evaluation.new.component';
import { AppEvaluationsListComponent } from './evaluations/component/app.evaluations.list.component';
import { AppStatisticsTableComponent } from './statistics/component/app.statistics.table.component';
import { AppStatisticsReliabilityComponent } from './statistics/component/app.statistics.reliability.component';
import { AppStatisticsChartsComponent } from './statistics/component/app.statistics.charts.component';

// Services
import { AppAssignmentService } from './assignments/service/app.assignment.service';
import { AppStatisticsService } from './statistics/service/app.statistics.service';
import { AppCoreCanDeactivateGuardService } from '../core/service/app.core.can.deactivate.guard.service';
import { AppEvaluationService } from './evaluations/service/app.evaluation.service';

// Pipes
import { AppCoreCharactersLimitPipe } from '../core/pipe/app.core.characters.limit.pipe';

// -----

@NgModule ({
  imports: [
    CommonModule,
    FormsModule,
    NgbModule,
    BsDropdownModule,
    AngularFontAwesomeModule,
    AppEvaluationsRoutingModule,
    AppCoreFormsModule,
    TabsModule,
    PopoverModule,
    ChartModule
  ],
  declarations: [
    AppCoreCharactersLimitPipe,
    AppAssignmentSummaryComponent,
    AppAssignmentsListComponent,
    AppAssignmentCorrectionSummaryComponent,
    AppAssignmentCorrectionsListComponent,
    AppAssignmentCorrectionsReceivedListComponent,
    AppEvaluationsListComponent,
    AppEvaluationEditComponent,
    AppEvaluationNewComponent,
    AppStatisticsTableComponent,
    AppStatisticsReliabilityComponent,
    AppStatisticsChartsComponent
  ],
  exports: [
    AppAssignmentSummaryComponent,
    AppAssignmentCorrectionSummaryComponent
  ],
  providers: [
    {
      provide: HighchartsStatic,
      useValue: Highcharts
    },
    AppEvaluationService,
    AppAssignmentService,
    AppStatisticsService,
    AppCoreCanDeactivateGuardService
  ]
})
export class AppEvaluationsModule {}

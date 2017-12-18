import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
import { ReactiveFormsModule } from '@angular/forms';
import { FileUploadModule } from 'ng2-file-upload';
import { MultiselectDropdownModule } from 'angular-2-dropdown-multiselect';
import { NouisliderModule } from 'ng2-nouislider';
import { ChartModule } from 'angular2-highcharts';
import { RouterModule } from '@angular/router';
import { AngularFontAwesomeModule } from 'angular-font-awesome';
import { AccordionModule, BsDatepickerModule, BsDropdownModule, PopoverModule, TabsModule, TimepickerModule, defineLocale } from 'ngx-bootstrap';
import { fr } from 'ngx-bootstrap/locale';
import { HighchartsStatic } from 'angular2-highcharts/dist/HighchartsService';
import * as Highcharts from 'highcharts';

// Modules
import { AppCoreFormService } from './service/app.core.form.service';
import { AppStudentImportComponent } from '../users/students/component/app.student.import.component';
import { AppAssignmentInstructionsComponent } from '../evaluations/assignments/component/app.assignment.instructions.component';
import { AppEvaluationSectionFormComponent } from '../evaluations/evaluations/component/app.evaluation.section.form.component';
import { AppAssignmentCorrectionInstructionsComponent } from '../evaluations/assignments/component/app.assignment.correction.instructions.component';
import { AppAssignmentCorrectionOpinionFormComponent } from '../evaluations/assignments/component/app.assignment.correction.opinion.form.component';

// Components
import { AppUserFormComponent } from '../users/users/component/app.user.form.component';
import { AppLessonFormComponent } from '../lessons/component/app.lesson.form.component';
import { AppGroupFormComponent } from '../groups/component/app.group.form.component';
import { AppSettingsFormComponent } from '../settings/component/app.settings.form.component';
import { AppAssignmentFormComponent } from '../evaluations/assignments/component/app.assignment.form.component';
import { AppAssignmentCorrectionFormComponent } from '../evaluations/assignments/component/app.assignment.correction.form.component';
import { AppEvaluationFormComponent } from '../evaluations/evaluations/component/app.evaluation.form.component';
import { AppEvaluationCorrectionFormComponent } from '../evaluations/evaluations/component/app.evaluation.correction.form.component';
import { AppEvaluationCorrectionCriteriaFormComponent } from '../evaluations/evaluations/component/app.evaluation.correction.criteria.form.component';
import { AppEvaluationCorrectionCriteriaChoiceFormComponent } from '../evaluations/evaluations/component/app.evaluation.correction.criteria.choice.form.component';
import { AppStatisticsParametersFormComponent } from '../evaluations/statistics/component/app.statistics.parameters.form.component';
import { AppEvaluationCorrectionAttributionsComponent } from '../evaluations/evaluations/component/app.evaluation.correction.attributions.component';
import { AppStatisticsFeedbacksFormComponent } from '../evaluations/statistics/component/app.statistics.feedbacks.form.component';
import { AppStatisticsCalibrationFormComponent } from '../evaluations/statistics/component/app.statistics.calibration.form.component';
import { AppCoreFormErrorsComponent } from './component/app.core.form.errors.component';

// Define
defineLocale('fr', fr);

// -----

@NgModule ({
  imports: [
    CommonModule,
    FormsModule,
    NgbModule,
    AccordionModule,
    TabsModule.forRoot(),
    BsDatepickerModule.forRoot(),
    TimepickerModule.forRoot(),
    PopoverModule.forRoot(),
    BsDropdownModule.forRoot(),
    MultiselectDropdownModule,
    AngularFontAwesomeModule,
    ReactiveFormsModule,
    RouterModule,
    FileUploadModule,
    NouisliderModule,
    ChartModule
  ],
  declarations: [
    AppUserFormComponent,
    AppStudentImportComponent,
    AppAssignmentInstructionsComponent,
    AppLessonFormComponent,
    AppGroupFormComponent,
    AppSettingsFormComponent,
    AppAssignmentFormComponent,
    AppAssignmentCorrectionFormComponent,
    AppEvaluationFormComponent,
    AppEvaluationSectionFormComponent,
    AppEvaluationCorrectionFormComponent,
    AppAssignmentCorrectionInstructionsComponent,
    AppAssignmentCorrectionOpinionFormComponent,
    AppEvaluationCorrectionCriteriaFormComponent,
    AppEvaluationCorrectionCriteriaChoiceFormComponent,
    AppStatisticsParametersFormComponent,
    AppEvaluationCorrectionAttributionsComponent,
    AppStatisticsFeedbacksFormComponent,
    AppStatisticsCalibrationFormComponent,
    AppCoreFormErrorsComponent,
  ],
  exports: [
    AppUserFormComponent,
    AppStudentImportComponent,
    AppAssignmentInstructionsComponent,
    AppLessonFormComponent,
    AppGroupFormComponent,
    AppSettingsFormComponent,
    AppAssignmentFormComponent,
    AppAssignmentCorrectionFormComponent,
    AppAssignmentCorrectionOpinionFormComponent,
    AppEvaluationFormComponent,
    AppEvaluationSectionFormComponent,
    AppEvaluationCorrectionFormComponent,
    AppEvaluationCorrectionCriteriaFormComponent,
    AppStatisticsParametersFormComponent,
    AppStatisticsFeedbacksFormComponent,
    AppStatisticsCalibrationFormComponent
  ],
  providers: [
    AppCoreFormService,
    {
      provide: HighchartsStatic,
      useValue: Highcharts
    }
  ]
})
export class AppCoreFormsModule {}

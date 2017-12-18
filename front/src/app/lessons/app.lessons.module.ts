import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
import { AccordionModule, BsDropdownModule } from 'ngx-bootstrap';
import { AngularFontAwesomeModule } from 'angular-font-awesome';

// Modules
import { AppLessonsRoutingModule } from './app.lessons.routing.module';
import { AppCoreFormsModule } from '../core/app.core.forms.module';
import { AppEvaluationsModule } from '../evaluations/app.evaluations.module';

// Components
import { AppLessonsAdminComponent } from './component/app.lessons.admin.component';
import { AppLessonsListComponent } from './component/app.lessons.list.component';
import { AppLessonEditAdminComponent } from './component/app.lesson.edit.admin.component';
import { AppLessonEditTeacherComponent } from './component/app.lesson.edit.teacher.component';
import { AppLessonNewComponent } from './component/app.lesson.new.component';
import { AppLessonSummaryComponent } from './component/app.lesson.summary.component';
import { AppLessonDetailsStudentComponent } from './component/app.lesson.details.student.component';
import { AppLessonDetailsTeacherComponent } from './component/app.lesson.details.teacher.component';

// Services
import { AppAuthAuthenticationService } from '../auth/service/app.auth.authentication.service';
import { AppCoreCanDeactivateGuardService } from '../core/service/app.core.can.deactivate.guard.service';

// -----

@NgModule ({
  imports: [
    CommonModule,
    FormsModule,
    AccordionModule,
    AppLessonsRoutingModule,
    AngularFontAwesomeModule,
    NgbModule,
    BsDropdownModule,
    AppCoreFormsModule,
    AppEvaluationsModule
  ],
  declarations: [
    AppLessonsAdminComponent,
    AppLessonsListComponent,
    AppLessonEditAdminComponent,
    AppLessonEditTeacherComponent,
    AppLessonNewComponent,
    AppLessonSummaryComponent,
    AppLessonDetailsStudentComponent,
    AppLessonDetailsTeacherComponent
  ],
  exports: [
    AppLessonSummaryComponent
  ],
  providers: [
    AppAuthAuthenticationService,
    AppCoreCanDeactivateGuardService
  ]
})
export class AppLessonsModule {}

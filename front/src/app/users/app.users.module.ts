import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
import { AngularFontAwesomeModule } from 'angular-font-awesome/angular-font-awesome';

// Modules
import { AppUsersRoutingModule } from './app.users.routing.module';
import { AppCoreFormsModule } from '../core/app.core.forms.module';

// Components
import { AppUserProfileComponent } from './users/component/app.user.profile.component';
import { AppStudentsAdminComponent } from './students/component/app.students.admin.component';
import { AppStudentEditComponent } from './students/component/app.student.edit.component';
import { AppStudentNewComponent } from './students/component/app.student.new.component';
import { AppTeachersAdminComponent } from './teachers/component/app.teachers.admin.component';
import { AppTeacherEditComponent } from './teachers/component/app.teacher.edit.component';
import { AppTeacherNewComponent } from './teachers/component/app.teacher.new.component';

// Services
import { AppCoreCanDeactivateGuardService } from '../core/service/app.core.can.deactivate.guard.service';

// -----

@NgModule ({
  imports: [
    CommonModule,
    FormsModule,
    NgbModule,
    AppUsersRoutingModule,
    AppCoreFormsModule,
    AngularFontAwesomeModule,
    AppCoreFormsModule
  ],
  declarations: [
    AppUserProfileComponent,
    AppStudentsAdminComponent,
    AppStudentEditComponent,
    AppStudentNewComponent,
    AppTeachersAdminComponent,
    AppTeacherEditComponent,
    AppTeacherNewComponent
  ],
  providers: [
    AppCoreCanDeactivateGuardService
  ]
})
export class AppUsersModule {}

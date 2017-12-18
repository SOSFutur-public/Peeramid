import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';

// Components
import { AppUserProfileComponent } from './users/component/app.user.profile.component';
import { AppStudentsAdminComponent } from './students/component/app.students.admin.component';
import { AppStudentNewComponent } from './students/component/app.student.new.component';
import { AppStudentImportComponent } from './students/component/app.student.import.component';
import { AppStudentEditComponent } from './students/component/app.student.edit.component';
import { AppTeachersAdminComponent } from './teachers/component/app.teachers.admin.component';
import { AppTeacherNewComponent } from './teachers/component/app.teacher.new.component';
import { AppTeacherEditComponent } from './teachers/component/app.teacher.edit.component';

// Services
import { AppAuthGuardService } from '../auth/service/app.auth.guard.service';
import { AppCoreCanDeactivateGuardService } from '../core/service/app.core.can.deactivate.guard.service';

// -----

const usersRoutes: Routes = [
  { path: 'admin',
    children: [
      { path: 'profile', component: AppUserProfileComponent, canActivate: [AppAuthGuardService], canDeactivate: [AppCoreCanDeactivateGuardService] },
      { path: 'students', component: AppStudentsAdminComponent, canActivate: [AppAuthGuardService] },
      {
        path: 'student',
        children: [
          { path: 'new', component: AppStudentNewComponent, canActivate: [AppAuthGuardService], canDeactivate: [AppCoreCanDeactivateGuardService] },
          { path: 'import', component: AppStudentImportComponent,  canActivate: [AppAuthGuardService] },
          { path: ':id/edit', component: AppStudentEditComponent, canActivate: [AppAuthGuardService], canDeactivate: [AppCoreCanDeactivateGuardService] },
        ]
      },
      { path: 'teachers', component: AppTeachersAdminComponent, canActivate: [AppAuthGuardService] },
      {
        path: 'teacher',
        children: [
          { path: 'new', component: AppTeacherNewComponent, canActivate: [AppAuthGuardService], canDeactivate: [AppCoreCanDeactivateGuardService] },
          { path: ':id/edit', component: AppTeacherEditComponent, canActivate: [AppAuthGuardService], canDeactivate: [AppCoreCanDeactivateGuardService] },
        ]
      }
    ],
  },
  { path: 'student/profile', component: AppUserProfileComponent, canActivate: [AppAuthGuardService], canDeactivate: [AppCoreCanDeactivateGuardService] },
  { path: 'teacher/profile', component: AppUserProfileComponent, canActivate: [AppAuthGuardService], canDeactivate: [AppCoreCanDeactivateGuardService] },
];

@NgModule({
  imports: [
    RouterModule.forChild(usersRoutes)
  ],
  exports: [
    RouterModule
  ]
})
export class AppUsersRoutingModule {}

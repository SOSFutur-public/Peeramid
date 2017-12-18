import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';

// Components
import { AppLessonsAdminComponent } from './component/app.lessons.admin.component';
import { AppLessonsListComponent } from './component/app.lessons.list.component';
import { AppLessonEditAdminComponent } from './component/app.lesson.edit.admin.component';
import { AppLessonEditTeacherComponent } from './component/app.lesson.edit.teacher.component';
import { AppLessonNewComponent } from './component/app.lesson.new.component';
import { AppLessonDetailsStudentComponent } from './component/app.lesson.details.student.component';
import { AppLessonDetailsTeacherComponent } from './component/app.lesson.details.teacher.component';

// Services
import { AppAuthGuardService } from '../auth/service/app.auth.guard.service';
import { AppCoreCanDeactivateGuardService } from '../core/service/app.core.can.deactivate.guard.service';

// -----

const lessonsRoutes: Routes = [
  {
    path: 'admin',
    children: [
      { path: 'lessons', component: AppLessonsAdminComponent, canActivate: [AppAuthGuardService] },
      { path: 'lesson',
        children: [
          { path: 'new', component: AppLessonNewComponent, canActivate: [AppAuthGuardService], canDeactivate: [AppCoreCanDeactivateGuardService] },
          { path: ':id/edit', component: AppLessonEditAdminComponent, canActivate: [AppAuthGuardService], canDeactivate: [AppCoreCanDeactivateGuardService] }
        ]
      }
    ]
  },
  {
    path: 'student',
    children: [
      { path: 'lessons', component: AppLessonsListComponent, canActivate: [AppAuthGuardService] },
      { path: 'lesson/:id', component: AppLessonDetailsStudentComponent, canActivate: [AppAuthGuardService] }
    ]
  },
  {
    path: 'teacher',
    children: [
      { path: 'lessons', component: AppLessonsListComponent, canActivate: [AppAuthGuardService] },
      { path: 'lesson/:id', component: AppLessonDetailsTeacherComponent, canActivate: [AppAuthGuardService] },
      { path: 'lesson/:id/edit', component: AppLessonEditTeacherComponent, canActivate: [AppAuthGuardService], canDeactivate: [AppCoreCanDeactivateGuardService] }
    ]
  },
];

@NgModule({
  imports: [
    RouterModule.forChild(lessonsRoutes)
  ],
  exports: [
    RouterModule
  ]
})
export class AppLessonsRoutingModule {}

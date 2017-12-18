import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';

// Components
import { AppHomeComponent } from './component/app.home.component';
import { AppHomeAdminComponent } from './component/app.home.admin.component';
import { AppHomeStudentComponent } from './component/app.home.student.component';
import { AppHomeTeacherComponent } from './component/app.home.teacher.component';

// Services
import { AppAuthGuardService } from '../auth/service/app.auth.guard.service';

// -----

const homeRoutes: Routes = [
  { path: 'home', component: AppHomeComponent, canActivate: [AppAuthGuardService] },
  { path: 'admin/home', component: AppHomeAdminComponent, canActivate: [AppAuthGuardService] },
  { path: 'student/home', component: AppHomeStudentComponent, canActivate: [AppAuthGuardService] },
  { path: 'teacher/home', component: AppHomeTeacherComponent, canActivate: [AppAuthGuardService] }
];

@NgModule({
  imports: [
    RouterModule.forChild(homeRoutes)
  ],
  exports: [
    RouterModule
  ]
})
export class AppHomeRoutingModule {}

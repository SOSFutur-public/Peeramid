import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

// Modules
import { AppHomeRoutingModule } from './app.home.routing.module';
import { AppEvaluationsModule } from '../evaluations/app.evaluations.module';
import { AppLessonsModule } from '../lessons/app.lessons.module';

// Components
import { AppHomeAdminComponent } from './component/app.home.admin.component';
import { AppHomeStudentComponent } from './component/app.home.student.component';
import { AppHomeTeacherComponent } from './component/app.home.teacher.component';
import { AppHomeComponent } from './component/app.home.component';

// -----

@NgModule ({
  imports: [
    CommonModule,
    AppHomeRoutingModule,
    AppEvaluationsModule,
    AppLessonsModule
  ],
  declarations: [
    AppHomeComponent,
    AppHomeAdminComponent,
    AppHomeStudentComponent,
    AppHomeTeacherComponent
  ]
})
export class AppHomeModule {}

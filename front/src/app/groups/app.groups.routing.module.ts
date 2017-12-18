import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';

// Components
import { AppGroupsAdminComponent } from './component/app.groups.admin.component';
import { AppGroupsStudentComponent } from './component/app.groups.student.component';
import { AppGroupEditComponent } from './component/app.group.edit.component';
import { AppGroupNewComponent } from './component/app.group.new.component';

// Services
import { AppAuthGuardService } from '../auth/service/app.auth.guard.service';

// -----

const groupsRoutes: Routes = [
  { path: 'admin/groups', component: AppGroupsAdminComponent, canActivate: [AppAuthGuardService] },
  { path: 'admin/group',
    children: [
      { path: 'new', component: AppGroupNewComponent, canActivate: [AppAuthGuardService] },
      { path: ':id/edit', component: AppGroupEditComponent, canActivate: [AppAuthGuardService] }
    ]
  },
  { path: 'student/groups', component: AppGroupsStudentComponent, canActivate: [AppAuthGuardService] }
];

@NgModule({
  imports: [
    RouterModule.forChild(groupsRoutes)
  ],
  exports: [
    RouterModule
  ]
})
export class AppGroupsRoutingModule {}

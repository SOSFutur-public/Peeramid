import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';

// Components
import { AppSettingsFormComponent } from './component/app.settings.form.component';

// Services
import { AppAuthGuardService } from '../auth/service/app.auth.guard.service';
import { AppCoreCanDeactivateGuardService } from '../core/service/app.core.can.deactivate.guard.service';

// -----

const settingsRoutes: Routes = [
  { path: 'admin/settings', component: AppSettingsFormComponent, canActivate: [AppAuthGuardService], canDeactivate: [AppCoreCanDeactivateGuardService] }
];

@NgModule({
  imports: [
    RouterModule.forChild(settingsRoutes)
  ],
  exports: [
    RouterModule
  ]
})
export class AppSettingsRoutingModule {}

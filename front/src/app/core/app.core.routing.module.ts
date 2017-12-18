import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';

// Components
import { AppError404Component } from './component/app.core.error-404.component';

// Services
import { AppAuthGuardService } from '../auth/service/app.auth.guard.service';

// -----

const appRoutes: Routes = [
  {
    path: '',
    redirectTo: 'home',
    pathMatch: 'full'
  },
  {
    path: '**',
    component: AppError404Component,
    canActivate: [AppAuthGuardService]
  }
];

@NgModule({
  imports: [
    RouterModule.forRoot(appRoutes)
  ],
  exports: [
    RouterModule
  ]
})
export class AppCoreRoutingModule {}

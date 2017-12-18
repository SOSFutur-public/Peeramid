import { NgModule } from '@angular/core';
import { RouterModule } from '@angular/router';

// Components
import { AppAuthAuthenticationComponent } from './component/app.auth.authentication.component';
import { AppAuthRequestComponent } from './component/app.auth.request.component';
import { AppAuthResetComponent } from './component/app.auth.reset.component';

// Services
import { AppAuthGuardService } from './service/app.auth.guard.service';

// -----

export const authRoutes = [
  {
    path: 'login',
    children: [
      { path: 'request', component: AppAuthRequestComponent },
      { path: 'reset/:token', component: AppAuthResetComponent },
      { path: '', component: AppAuthAuthenticationComponent }
    ]
  },
];

@NgModule({
  imports: [
    RouterModule.forChild(authRoutes)
  ],
  exports: [
    RouterModule
  ],
  providers: [
    AppAuthGuardService,
  ]
})
export class AppAuthRoutingModule { }

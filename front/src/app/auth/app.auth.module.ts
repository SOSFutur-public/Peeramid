import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule } from '@angular/forms';

// Modules
import { AppAuthRoutingModule } from './app.auth.routing';

// Components
import { AppAuthAuthenticationComponent } from './component/app.auth.authentication.component';
import { AppAuthAuthenticationService } from './service/app.auth.authentication.service';
import { AppAuthRequestComponent } from './component/app.auth.request.component';
import { AppAuthResetComponent } from './component/app.auth.reset.component';

// -----

@NgModule({
  imports: [
    CommonModule,
    AppAuthRoutingModule,
    ReactiveFormsModule,
  ],
  declarations: [
    AppAuthAuthenticationComponent,
    AppAuthRequestComponent,
    AppAuthResetComponent
  ],
  providers: [
    AppAuthAuthenticationService
  ],
  bootstrap: []
})
export class AppAuthModule { }

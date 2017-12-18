import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { AngularFontAwesomeModule } from 'angular-font-awesome/angular-font-awesome';

// Modules
import { AppSettingsRoutingModule } from './app.settings.routing.module';
import { AppCoreFormsModule } from '../core/app.core.forms.module';

// Services
import { AppCoreCanDeactivateGuardService } from '../core/service/app.core.can.deactivate.guard.service';

// -----

@NgModule ({
  imports: [
    CommonModule,
    AngularFontAwesomeModule,
    AppSettingsRoutingModule,
    AppCoreFormsModule
  ],
  providers: [
    AppCoreCanDeactivateGuardService
  ]
})
export class AppSettingsModule {}

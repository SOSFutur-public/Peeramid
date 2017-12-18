import {ErrorHandler, NgModule} from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
import { Http, HttpModule, RequestOptions } from '@angular/http';
import { ReactiveFormsModule } from '@angular/forms';
import { AngularFontAwesomeModule } from 'angular-font-awesome/angular-font-awesome';
import { AccordionModule, BsDropdownModule } from 'ngx-bootstrap';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';
import { AuthConfig, AuthHttp } from 'angular2-jwt';

// Modules
import { AppLessonsModule } from '../lessons/app.lessons.module';
import { AppGroupsModule } from '../groups/app.groups.module';
import { AppEvaluationsModule } from '../evaluations/app.evaluations.module';
import { AppSettingsModule } from '../settings/app.settings.module';
import { AppHomeModule } from '../home/app.home.module';
import { AppCoreRoutingModule } from './app.core.routing.module';
import { AppAuthModule } from '../auth/app.auth.module';
import { AppUsersModule } from '../users/app.users.module';

// Components
import { AppComponent } from './component/app.core.component';
import { AppNavbarComponent } from './component/app.core.navbar.component';
import { AppMenubarComponent } from './component/app.core.menubar.component';
import { AppError404Component } from './component/app.core.error-404.component';

// Services
import { AppAuthGuardService } from '../auth/service/app.auth.guard.service';
import { AppCoreFilterService } from './service/app.core.filter.service';
import { AppCoreLoaderService } from './service/app.core.loader.service';
import { AppCoreRestService } from './service/app.core.rest.service';
import { AppCoreAlertService } from './service/app.core.alert.service';
import { AppAuthAuthenticationService } from '../auth/service/app.auth.authentication.service';
import { AppCoreDataService } from './service/app.core.data.service';
import {AppGlobalErrorHandlerService} from "./service/app.global.error.handler.service";

// Exports
export function authHttpServiceFactory(http: Http, options: RequestOptions) {
  return new AuthHttp( new AuthConfig({
    headerName: 'Authorization',
    headerPrefix: 'Bearer',
    tokenName: 'token',
    tokenGetter: (() => localStorage.getItem('peeramid_token')),
    globalHeaders: [{ 'Content-Type': 'application/json' }],
    noJwtError: true,
  }), http, options);
}

// -----

@NgModule({
  declarations: [
    AppComponent,
    AppNavbarComponent,
    AppMenubarComponent,
    AppError404Component,
  ],
  imports: [
    BrowserModule,
    NgbModule.forRoot(),
    BsDropdownModule.forRoot(),
    AccordionModule.forRoot(),
    BsDropdownModule.forRoot(),
    HttpModule,
    ReactiveFormsModule,
    BrowserAnimationsModule,
    AngularFontAwesomeModule,
    AppAuthModule,
    AppHomeModule,
    AppLessonsModule,
    AppUsersModule,
    AppGroupsModule,
    AppEvaluationsModule,
    AppSettingsModule,
    AppCoreRoutingModule,
  ],
  providers: [
    AuthHttp,
    AppAuthGuardService,
    AppAuthAuthenticationService,
    AppCoreRestService,
    AppCoreFilterService,
    AppCoreAlertService,
    AppCoreLoaderService,
    AppCoreDataService,
    {
      provide: AuthHttp,
      useFactory: authHttpServiceFactory,
      deps: [Http, RequestOptions]
    },
    {
      provide: ErrorHandler,
      useClass: AppGlobalErrorHandlerService
    }
  ],
  bootstrap: [
    AppComponent
  ]
})
export class AppModule {}

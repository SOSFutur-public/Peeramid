import { ErrorHandler, Injectable, Injector } from '@angular/core';
import { LocationStrategy, PathLocationStrategy } from '@angular/common';
import { AppCoreRestService } from './app.core.rest.service';

@Injectable()
export class AppGlobalErrorHandlerService implements ErrorHandler {

  constructor(
    private injector: Injector
  ) {}

  handleError(error: any): void {
    let restService: AppCoreRestService;
    let location: LocationStrategy;
    let title: string;
    let stack: string;
    let url: string;

    restService = this.injector.get(AppCoreRestService);
    location = this.injector.get(LocationStrategy);
    title = ( error.message ? error.message : error.toString );
    stack = ( error.stack ? error.stack : error.toString() );
    url = ( location instanceof PathLocationStrategy ? location.path() : '' );

    restService.addDb('error', {error: `Url:\n${url}\n\nError:\n${title}\n\nStack:\n${stack}`});
    throw error;
  }

}

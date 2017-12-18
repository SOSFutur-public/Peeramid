import { Injectable } from '@angular/core';
import { CanDeactivate } from '@angular/router';
import { Observable } from 'rxjs/Observable';

// Exports
export interface ComponentToDeactivate {
  canDeactivate: () => Observable<boolean> | Promise<boolean> | boolean;
}

// -----

@Injectable ()
export class AppCoreCanDeactivateGuardService implements CanDeactivate<ComponentToDeactivate> {

  canDeactivate(component: ComponentToDeactivate) {
    return (component && component.canDeactivate) ? component.canDeactivate() : true;
  }

}

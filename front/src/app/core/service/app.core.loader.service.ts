import { Injectable, OnDestroy } from '@angular/core';
import { BehaviorSubject } from 'rxjs/BehaviorSubject';

// Services
import { AppCoreAlertService } from './app.core.alert.service';

// -----

@Injectable ()
export class AppCoreLoaderService {

  loading: BehaviorSubject<boolean> = new BehaviorSubject<boolean>(false);
  multipleLoading: number = 0;

  constructor(
    private alertService: AppCoreAlertService
  ) {
    this.loading.subscribe(isLoading => {
      if (!isLoading && this.alertService.waitingAlert.message !== null) {
        this.alertService.setAlert(this.alertService.waitingAlert.message, this.alertService.waitingAlert.alertClass);
        this.alertService.resetWaitingAlert();
      }
    });
  }

  display(value: boolean) {
    if (value) {
      if (this.loading.getValue()) {
        this.multipleLoading++;
      }
      this.loading.next(true);
    } else
    if (!value) {
      if (!(this.multipleLoading > 0)) {
        this.loading.next(false);
      }
      this.multipleLoading -= ( this.multipleLoading !== 0 ? 1 : 0 );
    }
  }

}

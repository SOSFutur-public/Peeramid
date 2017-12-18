import { Injectable } from '@angular/core';

// -----

@Injectable ()
export class AppCoreAlertService {

  alert: String = 'hide';
  alertClass: String;
  alertMessage: String;
  waitingAlert: any;

  constructor() {
    this.waitingAlert = {
      message: null,
      alertClass: null
    };
  }

  setAlert(message = null, alertClass = 'success', time = 4000): void {
    this.alertMessage = message;
    this.alertClass = alertClass;
    this.alert = 'show';
    setTimeout( () => { this.alert = 'hide'; }, time);
  }

  configWaitingAlert(message: string, alertClass: string = 'success'): void {
    if (this.waitingAlert.message !== null) {
      this.waitingAlert.message += '<br>' + message;
    } else {
      this.waitingAlert.message = message;
    }
    if (this.waitingAlert.alertClass !== 'error') {
      this.waitingAlert.alertClass = alertClass;
    }
  }

  resetWaitingAlert(): void {
    this.waitingAlert = {
      message: null,
      alertClass: null
    };
  }

}

import { Injectable } from '@angular/core';

// -----

@Injectable ()
export class AppCoreDataService {

  private routingAvoided;

  constructor() {
    setInterval(() => {
      this.routingAvoided = false;
    }, 10000);
  }

  avoidRouting(): void {
    this.routingAvoided = true;
  }

  allowRouting(): void {
    this.routingAvoided = false;
  }

  isRoutingAvoided(): boolean {
    return this.routingAvoided;
  }

}

import { Component } from '@angular/core';
import { Router } from '@angular/router';
import { environment } from '../../../environments/environment.prod';

// Services
import { AppAuthAuthenticationService } from '../../auth/service/app.auth.authentication.service';
import { AppCoreDataService } from '../service/app.core.data.service';

// -----

@Component({
  selector: 'app-menubar',
  templateUrl: '../html/app.core.menubar.component.html',
  styleUrls: ['../../../assets/css/app.core.menubar.component.scss']
})

export class AppMenubarComponent {

  environment = environment;

  constructor(
    public authService: AppAuthAuthenticationService,
    private router: Router,
    private dataService: AppCoreDataService
  ) {
    console.log('__CONSTRUCT__ app.menubar.component');
    this.authService.checkRole(['admin', 'student', 'teacher'], true);
  }

  isRoutingAvoided(): boolean {
    return this.dataService.isRoutingAvoided();
  }

  displayList(url: string): void {
    if (!this.isRoutingAvoided()) {
      this.dataService.avoidRouting();
      this.router.navigate([url]);
    }
  }

}


import { AfterViewChecked, ChangeDetectorRef, Component } from '@angular/core';
import { animate, state, style, transition, trigger } from '@angular/animations';

// Services
import { AppCoreAlertService } from '../service/app.core.alert.service';
import { AppAuthAuthenticationService } from '../../auth/service/app.auth.authentication.service';
import { AppCoreLoaderService } from '../service/app.core.loader.service';

// -----

@Component({
  selector: 'app-root',
  templateUrl: '../html/app.core.component.html',
  styleUrls: [
    '../../../assets/css/app.core.component.scss',
    '../../../assets/css/app.core.loader.scss'
  ],
  animations: [
    trigger('alertAnimation', [
      state('show', style({
        opacity: 1,
        top: '50px',
      })),
      state('hide', style({
        opacity: 0,
        top: '0',
      })),
      transition('show <=> hide', animate('500ms ease-out')),
    ]),
  ],
})
export class AppComponent implements AfterViewChecked {

  loadingComponent: boolean;
  ok: Boolean = false;

  constructor(
    private cdRef: ChangeDetectorRef,
    public authService: AppAuthAuthenticationService,
    private alertService: AppCoreAlertService,
    private loaderService: AppCoreLoaderService,
  ) {
    console.log('__CONSTRUCT__ app.core.component');
    this.cdRef.detach();
    setInterval(() => {
      this.cdRef.detectChanges();
    }, 1);
    if (authService.loggedIn()) {
      this.authService.getLoggedUser()
        .then(() => { this.ok = true; });
    } else {
      this.ok = true;
    }
  }

  ngAfterViewChecked(): void {
    this.loaderService.loading.subscribe((isLoading: boolean) => {
      this.loadingComponent = isLoading;
    });
  }

  loggedOut() {
    if (!this.authService.loggedIn() && this.authService.user !== null) {
      this.authService.logout(true);
    } else {
      return true;
    }
  }

}

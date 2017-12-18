import { Component, OnInit } from '@angular/core';
import {ActivatedRoute, Router} from '@angular/router';

// Classes
import { Correction } from '../../class/app.evaluation.class';

// Animations
import { routerAnimation } from '../../../../animations/router.animations';

// Services
import { AppCoreRestService } from '../../../core/service/app.core.rest.service';
import { AppAuthAuthenticationService } from '../../../auth/service/app.auth.authentication.service';
import { AppCoreAlertService } from '../../../core/service/app.core.alert.service';
import { AppCoreLoaderService } from '../../../core/service/app.core.loader.service';

// -----

@Component ({
  selector: 'app-assignment-corrections-list',
  templateUrl: '../html/app.assignment.corrections.list.component.html',
  animations: [routerAnimation],
  host: { '[@routerAnimation]': '' }
})
export class AppAssignmentCorrectionsListComponent implements OnInit {

  corrections: { individual_corrections: Correction[], group_corrections: Correction[] };
  status: string;

  constructor(
    private router: Router,
    private route: ActivatedRoute,
    private restService: AppCoreRestService,
    private authService: AppAuthAuthenticationService,
    private loaderService: AppCoreLoaderService,
    private alertService: AppCoreAlertService,
  ) {
    console.log('__CONSTRUCT__ app.corrections.list.component');
    this.authService.checkRole(['student'], true);
  }

  ngOnInit(): void {
    this.route.params.subscribe(
      params => {
        this.status = params['status'];
        if (this.getFrenchStatus(this.status) === null) {
          this.alertService.setAlert('Cette page n\'est pas reconnue...', 'warning');
          this.router.navigate(['/home']);
        } else {
          this.getCorrections(this.status);
        }
      });
  }

  getCorrections(status: string): void {
    this.loaderService.display(true);
    this.corrections = {
      individual_corrections: [],
      group_corrections: []
    };
    this.restService.getDb('userCorrections', null, status)
      .then(corrections => {
        corrections.individual_corrections.forEach(individual_correction => {
          this.corrections.individual_corrections.push(new Correction(individual_correction));
        });
        corrections.group_corrections.forEach(group_correction => {
          this.corrections.group_corrections.push(new Correction(group_correction));
        });
        this.loaderService.display(false);
      });
  }

  getFrenchStatus(status: string): string {
    switch (status) {
      case 'in-progress' : {
        return 'EN COURS';
      }
      case 'finished' : {
        return 'TERMINÉES';
      }
      default: {
        return null;
      }
    }
  }

}

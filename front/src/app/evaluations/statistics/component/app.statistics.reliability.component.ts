import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { isUndefined } from 'ngx-bootstrap/bs-moment/utils/type-checks';
// Classes
import { User } from '../../../users/class/app.user.class';
import { Group } from '../../../groups/class/app.group.class';

// Animations
import { routerAnimation } from '../../../../animations/router.animations';

// Services
import { AppCoreRestService } from '../../../core/service/app.core.rest.service';
import { AppAuthAuthenticationService } from '../../../auth/service/app.auth.authentication.service';
import { AppCoreLoaderService } from '../../../core/service/app.core.loader.service';

// -----

@Component ({
  selector: 'app-statistics-reliability',
  templateUrl: '../html/app.statistics.reliability.component.html',
  animations: [routerAnimation],
  host: { '[@routerAnimation]': '' }
})
export class AppStatisticsReliabilityComponent implements OnInit {

  reliabilities: {
    user: User,
    group: Group,
    criterias_reliability: any,
    average_reliability: number
  }[] = [];
  evaluationId: number;
  criteriaNames: string[];

  constructor(
    private route: ActivatedRoute,
    private router: Router,
    private restService: AppCoreRestService,
    private authService: AppAuthAuthenticationService,
    private loaderService: AppCoreLoaderService
  ) {
    this.authService.checkRole(['teacher'], true);
  }

  ngOnInit(): void {
    this.getReliability();
  }

  getReliability(): void {
    this.loaderService.display(true);
    this.evaluationId = +this.route.snapshot.params['id'];
    this.reliabilities = [];
    this.restService.getDb('evaluationStatsReliability', [this.evaluationId])
      .then(reliabilities => {
        reliabilities.forEach(reliability => {
          this.reliabilities.push({
            user: ( reliability.user ? new User(reliability.user) : null ),
            group: ( reliability.group ? new Group(reliability.group) : null ),
            criterias_reliability: this.setCriteriasReliability(reliability.criterias_reliability),
            average_reliability: ( typeof reliability.average_reliability === 'number' ? reliability.average_reliability : null )
          });
        });
        this.loaderService.display(false);
      });
  }

  setCriteriasReliability(criteriasReliability: any): number[] {
    if (isUndefined(this.criteriaNames)) {
      this.criteriaNames = Object.keys(criteriasReliability);
    }
    for (const criteriaName of this.criteriaNames) {
      criteriasReliability[criteriaName] = ( typeof criteriasReliability[criteriaName] === 'number' ? criteriasReliability[criteriaName] : null );
    }
    return criteriasReliability;
  }

  cancel(): void {
    this.router.navigate(['/teacher/statistics', this.evaluationId, 'global']);
  }

}

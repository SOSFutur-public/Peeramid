import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { isUndefined } from 'ngx-bootstrap/bs-moment/utils/type-checks';

// Classes
import { Criteria, Evaluation } from '../../class/app.evaluation.class';

// Animations
import { routerAnimation } from '../../../../animations/router.animations';

// Services
import { AppCoreRestService } from '../../../core/service/app.core.rest.service';
import { AppAuthAuthenticationService } from '../../../auth/service/app.auth.authentication.service';
import { AppCoreLoaderService } from '../../../core/service/app.core.loader.service';
import { AppCoreFilterService } from '../../../core/service/app.core.filter.service';

// -----

@Component ({
  selector: 'app-statistics-charts',
  templateUrl: '../html/app.statistics.charts.component.html',
  animations: [routerAnimation],
  host: { '[@routerAnimation]': '' }
})
export class AppStatisticsChartsComponent implements OnInit {

  evaluation: Evaluation;
  criteriaCharts: any[][];

  constructor(
    private route: ActivatedRoute,
    private router: Router,
    private restService: AppCoreRestService,
    private authService: AppAuthAuthenticationService,
    private loaderService: AppCoreLoaderService,
    private filterService: AppCoreFilterService
  ) {
    this.authService.checkRole(['teacher'], true);
  }

  ngOnInit(): void {
    this.getEvaluation();
  }

  getEvaluation(): void {
    let id: number;

    id = +this.route.snapshot.params['id'];
    this.loaderService.display(true);
    this.restService.getDb('evaluationStatsCharts', [id])
      .then(evaluation => this.evaluation = new Evaluation(evaluation))
      .then(() => {
        this.filterService.sortList(this.evaluation.sections, 'order', true);
        this.evaluation.sections.forEach(section => this.filterService.sortList(section.criterias, 'order', true));
      })
      .then(() => {
        this.getCriteriaCharts();
        this.loaderService.display(false);
      });
  }

  getCriteriaCharts(): void {
    this.criteriaCharts = [];

    this.evaluation.sections.forEach((section, sectionIndex) => {
      this.criteriaCharts.push([]);
      section.criterias.forEach((criteria, criteriaIndex) => {
        if (!isUndefined(criteria.chart) && this.criteriaGotMarks(criteria.chart)) {
          this.criteriaCharts[sectionIndex][criteriaIndex] = this.createCriteriaChart(this.evaluation.sections[sectionIndex].criterias[criteriaIndex]);
        } else {
          this.criteriaCharts[sectionIndex][criteriaIndex] = null;
        }
      });
    });
  }

  createCriteriaChart(criteria: Criteria): any {
    let names: string[];
    let data: any[];

    names = Object.keys(criteria.chart);
    data = [];
    names.forEach(name => {
      data.push({ name: name, y: criteria.chart[name] });
    });
    return {
      chart: { type: 'pie', style: { fontFamily: 'node_modules/font-awesome/fonts/FontAwesome.otf' } },
      title: { text: 'Critère ' + criteria.order + ': ' + criteria.description.replace(/(<([^>]+)>)/ig, '').substring(0, 30) + ( criteria.description.length > 30 ? '...' : '' ) },
      series: [{
        name: 'Notes',
        data: data,
        innerSize: '60%'
      }],
      credits: { enabled: false }
    };
  }

  criteriaGotMarks(chart: any): boolean {
    let gotMarks: boolean;

    gotMarks = false;
    for (const marks in chart) {
      if (chart[marks] > 0) {
        gotMarks = true;
      }
    }
    return gotMarks;
  }

  cancel(): void {
    this.router.navigate(['/teacher/statistics', this.evaluation.id, 'global']);
  }

}

import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { FormBuilder, FormControl, FormGroup } from '@angular/forms';
import { IMultiSelectOption, IMultiSelectSettings, IMultiSelectTexts } from 'angular-2-dropdown-multiselect';
import { isUndefined } from 'ngx-bootstrap/bs-moment/utils/type-checks';
const Highcharts = require('highcharts');

// Classes
import { Criteria, Evaluation } from '../../class/app.evaluation.class';

// Animations
import { routerAnimation } from '../../../../animations/router.animations';

// Services
import { AppCoreRestService } from '../../../core/service/app.core.rest.service';
import { AppCoreAlertService } from '../../../core/service/app.core.alert.service';
import { AppAuthAuthenticationService } from '../../../auth/service/app.auth.authentication.service';
import { AppCoreFormService } from '../../../core/service/app.core.form.service';
import { AppCoreLoaderService } from '../../../core/service/app.core.loader.service';
import { AppCoreFilterService } from '../../../core/service/app.core.filter.service';

// -----

@Component ({
  selector: 'app-statistics-calibration',
  templateUrl: '../html/app.statistics.calibration.form.component.html',
  animations: [routerAnimation],
  host: { '[@routerAnimation]': '' }
})
export class AppStatisticsCalibrationFormComponent implements OnInit {

  evaluation: Evaluation;
  criteria: Criteria;
  _criteria: any;
  // FORM
  calibrageForm: FormGroup;
  // CONTROLS
  criteriasControl: FormControl;
  slideControl: FormControl;
  // SELECT
  singleSelectSettings: IMultiSelectSettings;
  criteriasSelectOptions: IMultiSelectOption[];
  criteriasSelectTexts: IMultiSelectTexts;
  // CHART
  criteriaChart: any;
  differences: any;
  maxDiff: number;

  constructor(
    private route: ActivatedRoute,
    private router: Router,
    private restService: AppCoreRestService,
    private authService: AppAuthAuthenticationService,
    private loaderService: AppCoreLoaderService,
    private formService: AppCoreFormService,
    private alertService: AppCoreAlertService,
    private filterService: AppCoreFilterService,
    private formBuilder: FormBuilder,
  ) {
    console.log('__CONSTRUCT__ app.statistics.calibration.form.component');
    this.authService.checkRole(['teacher'], true);
  }

  ngOnInit(): void {
    // SELECT
    this.criteriasSelectTexts = this.formService.selectTexts(' un critère...');
    this.singleSelectSettings = this.formService.singleSelectSettings(false);
    // EVALUATION
    this.getEvaluation();
  }

  canDeactivate(): boolean {
    if (!isUndefined(this.slideControl) && this.slideControl.dirty) {
      return confirm('Êtes-vous sûr de quitter le formulaire sans enregistrer vos modifications?');
    }
    return true;
  }

  getEvaluation(): void {
    this.loaderService.display(true);
    this.restService.getDb('evaluations', [+this.route.snapshot.params['id']])
      .then(evaluation => this.evaluation = new Evaluation(evaluation))
      .then(() => {
        this.filterService.sortList(this.evaluation.sections, 'order', true);
        this.evaluation.sections.forEach(section => this.filterService.sortList(section.criterias, 'order', true));
      })
      .then(() => {
        console.log(this.evaluation);
        this.criteriasSelectOptions = [];
        this.evaluation.sections.forEach(section => {
          this.criteriasSelectOptions.push({
            id: section.id,
            name: section.title,
            isLabel: true
          });
          section.criterias.forEach(criteria => {
            if (criteria.criteria_type.id !== 1) {
              this.criteriasSelectOptions.push({
                id: criteria.id,
                name: 'Critère ' + criteria.order,
                parentId: section.id
              });
            }
          });
        });
      })
      .then(() => {
        this.setForm();
        this.loaderService.display(false);
      })
      .then(() => this.calibrageForm.valueChanges.subscribe(data => { this.getChart(); }));
  }

  // FORM
  setForm(): void {
    this.calibrageForm = this.formBuilder.group({
      criterias: this.criteriasControl = new FormControl(
        []
      ),
    });
  }


  // CHARTS
  updateTrapezium(): void {
    this.criteriaChart.series[1].data.forEach((data, index) => {
      data.update({ x: this.slideControl.value[index] });
    });
  }
  getChart(): void {
    let maxCorrectorNumber: number;
    let xAxisData: string[];
    let chartData: any[];

    chartData = [];
    if (this.criteriasControl.value.length > 0) {
      this.loaderService.display(true);
      this.restService.getDb('evaluationStatsTrapezium', [this.criteriasControl.value])
        .then(criteria => {
          this.criteria = new Criteria({ id: criteria.id, trapezium: criteria.trapezium });
          this.differences = criteria.differences;
          this.maxDiff = criteria.max_diff;
        })
        .then(() => {
          this.slideControl = new FormControl([this.criteria.trapezium.min0, this.criteria.trapezium.min100, this.criteria.trapezium.max100, this.criteria.trapezium.max0]);
          xAxisData = Object.keys(this.differences);
          maxCorrectorNumber = 0;
          xAxisData.forEach(x => {
            maxCorrectorNumber = ( parseInt(this.differences[x], 10) ? parseInt(this.differences[x], 10) : maxCorrectorNumber );
            chartData.push([parseFloat(x), parseFloat(this.differences[x])]);
          });
          chartData.sort((a, b) => {
            return a < b ? -1 : (a > b ? 1 : 0);
          });
          this.criteriaChart = new Highcharts.chart('criteriaChart', {
            chart: {
              style: {
                fontFamily: 'node_modules/font-awesome/fonts/FontAwesome.otf'
              },
            },
            title: {
              text: 'Fonction de fiabilité'
            },
            xAxis: [{
              title: {
                text: 'Écart type'
              },
              tickInterval: 1,
              min: this.maxDiff * -1,
              max: this.maxDiff
            }],
            yAxis: [{
              title: {
                text: 'Nombre de correcteurs',
                style: {
                  color: '#337ab7'
                }
              },
              labels: {
                style: {
                  color: '#337ab7'
                }
              },
              allowDecimals: false
            }, {
              title: {
                text: 'Échelle de fiabilité',
                style: {
                  color: '#961e64'
                },
              },
              labels: {
                format: '{value} %',
                style: {
                  color: '#961e64'
                },
              },
              min: 0,
              max: 100,
              tickInterval: 25,
              opposite: true
            }],
            series: [{
              type: 'column',
              name: 'Nombre de correcteur par note',
              data: chartData,
              color: '#337ab7'
            }, {
              type: 'area',
              name: 'Fiabilité',
              yAxis: 1,
              data: [{
                x: this.criteria.trapezium.min0,
                y: 0
              }, {
                x: this.criteria.trapezium.min100,
                y: 100
              }, {
                x: this.criteria.trapezium.max100,
                y: 100
              }, {
                x: this.criteria.trapezium.max0,
                y: 0
              }],
              tooltip: {
                valueSuffix: '%'
              },
              color: '#961e64'
            }],
            plotOptions: {
              series: {
                fillOpacity: 0.25
              }
            },
            credits: {
              enabled: false
            }
          });
          this.loaderService.display(false);
        });
    }
  }

  // SAVE / CANCEL
  saveReliability(): void {
    this.loaderService.display(true);
    this.applyFormData();
    this.restService.updateDb('evaluationStatTrapezium', this._criteria)
      .then(response => {
        if (response.success) {
          this.slideControl.markAsPristine();
          this.alertService.configWaitingAlert('La fonction de fiabilité a bien été enregistré.');
        }
        this.loaderService.display(false);
      })
      .catch(() => {
        console.error(`Reliability function cannot be changed in the database.`);
        this.alertService.configWaitingAlert('Une erreur est survenue...', 'error');
        this.loaderService.display(false);
      });
  }

  cancel(): void {
    this.router.navigate(['/teacher/statistics', this.evaluation.id, 'global']);
  }
  applyFormData(): void {
    this._criteria = Object.assign({}, this.criteria);
    this._criteria.trapezium.min0 = this.slideControl.value[0];
    this._criteria.trapezium.min100 = this.slideControl.value[1];
    this._criteria.trapezium.max100 = this.slideControl.value[2];
    this._criteria.trapezium.max0 = this.slideControl.value[3];
  }

}

import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { saveAs as importedSaveAs } from 'file-saver';
import { isUndefined } from 'ngx-bootstrap/bs-moment/utils/type-checks';

// Environment
import { environment } from '../../../../environments/environment';

// Classes
import { Assignment, AssignmentCriteria, AssignmentSection, Correction, CorrectionCriteria, Evaluation } from '../../class/app.evaluation.class';
import { StatisticsState } from '../class/app.statistics.class';

// Animations
import { routerAnimation } from '../../../../animations/router.animations';

// Services
import { AppCoreRestService } from '../../../core/service/app.core.rest.service';
import { AppAuthAuthenticationService } from '../../../auth/service/app.auth.authentication.service';
import { AppCoreLoaderService } from '../../../core/service/app.core.loader.service';
import { AppStatisticsService } from '../service/app.statistics.service';
import { AppCoreAlertService } from '../../../core/service/app.core.alert.service';

// -----

@Component ({
  selector: 'app-statistics-criterion',
  templateUrl: '../html/app.statistics.table.component.html',
  styleUrls: ['../../../../assets/css/app.statistics.criterion.component.scss'],
  animations: [routerAnimation],
  host: { '[@routerAnimation]': '' }
})
export class AppStatisticsTableComponent implements OnInit {

  environment = environment;
  evaluation: Evaluation;
  teacherCorrections: Correction[];
  opinionCommentsToDisplay: string;
  state: StatisticsState;
  statsType: string;

  constructor(
    private route: ActivatedRoute,
    private router: Router,
    private restService: AppCoreRestService,
    private authService: AppAuthAuthenticationService,
    private alertService: AppCoreAlertService,
    private loaderService: AppCoreLoaderService,
    private statisticsService: AppStatisticsService
  ) {
    console.log('__CONSTRUCT__ app.statistics.criterion.component');
    this.authService.checkRole(['teacher'], true);
  }

  ngOnInit(): void {
    this.statsType = null;
    this.route.params.subscribe(
      params => {
        this.statsType = params['stats-type'];
        if (this.statsType !== 'global' && this.statsType !== 'criterion') {
          this.alertService.setAlert('Cette page n\'est pas reconnue...', 'warning');
          this.router.navigate(['/home']);
        } else {
          this.getEvaluation();
        }
      });
  }

  getEvaluation(): void {
    let id: number;
    let urlName: string;

    id = +this.route.snapshot.params['id'];
    urlName = ( this.statsType === 'criterion' ? 'evaluationStatsCriterias' : 'evaluationStats' );
    this.loaderService.display(true);
    this.restService.getDb(urlName, [id])
      .then(evaluation => this.evaluation = new Evaluation(evaluation, true))
      .then(() => {
        this.state = this.statisticsService.getState(this.evaluation);
        this.getTeacherCorrections();
        this.loaderService.display(false);
      });
  }

  getTeacherCorrections(): void {
    let index: number;

    this.teacherCorrections = [];
    this.evaluation.assignments.forEach(assignment => {
      index = assignment.corrections.findIndex(correction => {
        return correction.user.role.id === 3;
      });
      if (index >= 0) {
        assignment.corrections[index].assignment = new Assignment({ id: assignment.id });
        this.teacherCorrections.push(assignment.corrections[index]);
        assignment.corrections.splice(index, 1);
      }
    });
  }

  findTeacherCorrection(assignment: Assignment): Correction {
    return this.teacherCorrections.find(teacherCorrection => {
      return teacherCorrection.assignment.id === assignment.id;
    });
  }

  findTeacherCorrectionCriteria(assignment: Assignment, correctionCriteria: CorrectionCriteria): CorrectionCriteria {
    let teacherCorrection: Correction;
    let teacherCorrectionCriteriaTmp: CorrectionCriteria;
    let teacherCorrectionCriteriaFound: CorrectionCriteria;

    teacherCorrectionCriteriaFound = null;
    teacherCorrectionCriteriaTmp = null;
    if (!isUndefined(teacherCorrection = this.findTeacherCorrection(assignment))) {
      teacherCorrection.correction_sections.forEach(teacherCorrectionSection => {
        teacherCorrectionCriteriaFound = teacherCorrectionSection.correction_criterias.find(teacherCorrectionCriteria => {
          return teacherCorrectionCriteria.criteria.id === correctionCriteria.criteria.id;
        });
        teacherCorrectionCriteriaTmp = ( !isUndefined(teacherCorrectionCriteriaFound) ? teacherCorrectionCriteriaFound : teacherCorrectionCriteriaTmp );
      });
    }
    return teacherCorrectionCriteriaTmp;
  }

  getAssignmentCriteria(correctionCriteria: CorrectionCriteria, assignmentSections: AssignmentSection[]): AssignmentCriteria {
    let assignmentCriteriaTmp: AssignmentCriteria;

    for (const assignmentSection of assignmentSections) {
      assignmentCriteriaTmp = assignmentSection.assignment_criterias.find(assignmentCriteria => {
        return assignmentCriteria.criteria.id === correctionCriteria.criteria.id;
      });
      if (!isUndefined(assignmentCriteriaTmp)) {
        return assignmentCriteriaTmp;
      }
    }
    return null;
  }

  setOpinionCommentsToDisplay(criteria: CorrectionCriteria): void {
    this.opinionCommentsToDisplay = criteria.correction_opinion.comments;
  }

  cancel(): void {
    this.router.navigate(['/teacher/statistics', this.evaluation.id, 'global']);
  }

  exportStats(): void {
    this.loaderService.display(true);
    this.restService.downloadFile('exportStats', [this.evaluation.id]).subscribe(blob => {
      importedSaveAs(blob, 'Export_evaluation_' + this.evaluation.id + '_' + new Date().toJSON().slice(0, 10).replace(/-/g, '') + '_' + new Date().toJSON().slice(11, 16).replace(/:/g, '') + '.zip');
      this.loaderService.display(false);
    });
  }

}

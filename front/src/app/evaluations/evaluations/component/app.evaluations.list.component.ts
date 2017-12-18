import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';

// Classes
import { Evaluation } from '../../class/app.evaluation.class';

// Animations
import { routerAnimation } from '../../../../animations/router.animations';

// Services
import { AppCoreRestService } from '../../../core/service/app.core.rest.service';
import { AppAuthAuthenticationService } from '../../../auth/service/app.auth.authentication.service';
import { AppCoreAlertService } from '../../../core/service/app.core.alert.service';
import { AppCoreDataService } from '../../../core/service/app.core.data.service';
import { AppCoreLoaderService } from '../../../core/service/app.core.loader.service';

// -----

@Component ({
  selector: 'app-evaluations-list',
  templateUrl: '../html/app.evaluations.list.component.html',
  animations: [routerAnimation],
  host: { '[@routerAnimation]': '' }
})
export class AppEvaluationsListComponent implements OnInit {

  evaluations: Evaluation[] = [];
  status: string;

  constructor(
    private route: ActivatedRoute,
    private router: Router,
    private restService: AppCoreRestService,
    private authService: AppAuthAuthenticationService,
    private loaderService: AppCoreLoaderService,
    private alertService: AppCoreAlertService,
    private dataService: AppCoreDataService
  ) {
    console.log('__CONSTRUCT__ app.evaluation.list.component');
    this.authService.checkRole(['teacher'], true);
  }

  ngOnInit(): void {
    this.status = null;
    this.route.params.subscribe(
      params => {
        this.status = params['status'];
        if (this.getStatus() === null) {
          this.alertService.setAlert('Cette page n\'est pas reconnue...', 'warning');
          this.router.navigate(['/home']);
        } else {
          this.getEvaluations();
        }
      }
    );
  }

  getEvaluations(): void {
    this.loaderService.display(true);
    this.evaluations = [];
    this.restService.getDb('evaluationsList', null, this.status)
      .then(response => {
        response.forEach(evaluation => {
          this.evaluations.push(new Evaluation(evaluation));
        });
        this.loaderService.display(false);
      })
      .then(() => this.dataService.allowRouting());
  }

  getStatus(): string {
    switch (this.status) {
      case 'draft' : {
        return 'BROUILLONS';
      }
      case 'incoming' : {
        return 'À VENIR';
      }
      case 'in-progress' : {
        return 'EN COURS';
      }
      case 'finished' : {
        return 'TERMINÉES';
      }
      case 'archived' : {
        return 'ARCHIVÉES';
      }
      default: {
        return null;
      }
    }
  }

  deleteEvaluation(evaluation: Evaluation): void {
    let index: number;

    if (confirm(`Etes vous sûr de vouloir supprimer l'évaluation \'${evaluation.name}\'?`)) {
      this.loaderService.display(true);
      this.restService.deleteDb('evaluations', [evaluation.id])
        .then(response => {
          if (response.success) {
            index = this.evaluations.indexOf(evaluation);
            this.evaluations.splice(index, 1);
            this.alertService.setAlert(`L'évaluation \'${evaluation.name}\' a bien été supprimée`);
          } else {
            console.error(`Evaluation \'${evaluation.name}\' cannot be deleted in the database.`);
            this.alertService.setAlert('Une erreur est survenue...', 'error');
          }
          this.loaderService.display(false);
        })
        .catch(() => {
          console.error(`Evaluation \'${evaluation.name}\' cannot be deleted in the database.`);
          this.alertService.setAlert('Une erreur est survenue...', 'error');
          this.loaderService.display(false);
        });
    }
  }

  duplicateEvaluation(evaluation: Evaluation): void {
    this.loaderService.display(true);
    this.restService.duplicateDb('evaluationDuplication', [evaluation.id])
      .then(response => {
        if (response.success) {
          if (this.status === 'draft') {
            this.evaluations.push(new Evaluation(response.evaluation));
          }
          this.alertService.setAlert(`L'évaluation \'${evaluation.name}\' a bien été dupliquée`);
        } else {
          console.error(`Evaluation \'${evaluation.name}\' cannot be duplicated in the database.`);
          this.alertService.setAlert('Une erreur est survenue...', 'error');
        }
        this.loaderService.display(false);
      })
      .catch(() => {
        console.error(`Evaluation \'${evaluation.name}\' cannot be duplicated in the database.`);
        this.alertService.setAlert('Une erreur est survenue...', 'error');
        this.loaderService.display(false);
      });
  }

  isArchivable(evaluation: Evaluation): boolean {
    return (this.status === 'finished'
      && (!evaluation.date_end_opinion || (evaluation.date_end_opinion && evaluation.date_end_opinion < new Date())));
  }

  archiveEvaluation(evaluationToArchive): void {
    let index: number;

    this.loaderService.display(true);
    this.restService.updateDb('evaluationArchiving', evaluationToArchive)
      .then(response => {
        if (response.success) {
          index = this.evaluations.findIndex(evaluation => evaluation.id === response.evaluation.id);
          this.evaluations.splice(index, 1);
          this.alertService.setAlert(`L'évaluation \'${evaluationToArchive.name}\' a bien été archivée`);
        } else {
          console.error(`Evaluation \'${evaluationToArchive.name}\' cannot be archived in the database.`);
          this.alertService.setAlert('Une erreur est survenue...', 'error');
        }
        this.loaderService.display(false);
      })
      .catch(() => {
        console.error(`Evaluation \'${evaluationToArchive.name}\' cannot be archived in the database.`);
        this.alertService.setAlert('Une erreur est survenue...', 'error');
        this.loaderService.display(false);
      });
  }

}

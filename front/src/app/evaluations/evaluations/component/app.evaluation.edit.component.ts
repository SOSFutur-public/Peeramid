import { Component, OnInit } from '@angular/core';
import {ActivatedRoute } from '@angular/router';

// Classes
import { Evaluation } from '../../class/app.evaluation.class';

// Animations
import { routerAnimation } from '../../../../animations/router.animations';

// Services
import { AppCoreRestService } from '../../../core/service/app.core.rest.service';
import { AppAuthAuthenticationService } from '../../../auth/service/app.auth.authentication.service';
import { AppCoreLoaderService } from '../../../core/service/app.core.loader.service';

// -----

@Component ({
  selector: 'app-evaluation-edit',
  templateUrl: '../html/app.evaluation.edit.component.html',
  animations: [routerAnimation],
  host: { '[@routerAnimation]': '' }
})
export class AppEvaluationEditComponent implements OnInit {

  editEvaluation: Evaluation;
  dirty: boolean;

  constructor(
    private route: ActivatedRoute,
    private restService: AppCoreRestService,
    private authService: AppAuthAuthenticationService,
    private loaderService: AppCoreLoaderService
  ) {
    console.log('__CONSTRUCT__ app.evaluation.edit.component');
    this.authService.checkRole(['teacher'], true);
  }

  ngOnInit(): void {
    this.getEvaluation(+this.route.snapshot.params['id']);
  }

  canDeactivate(): boolean {
    if (this.dirty) {
      return confirm('Êtes-vous sûr de quitter le formulaire sans enregistrer vos modifications?');
    }
    return true;
  }

  getEvaluation(id: number): void {
    this.loaderService.display(true);
    this.restService.getDb('evaluations', [id])
      .then(evaluation => {
        this.editEvaluation = new Evaluation(evaluation);
        this.loaderService.display(false);
      });
  }

}

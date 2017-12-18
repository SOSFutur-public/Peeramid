import { Component, OnInit } from '@angular/core';

// Classes
import { Evaluation } from '../../class/app.evaluation.class';

// Animations
import { routerAnimation } from '../../../../animations/router.animations';

// Services
import { AppAuthAuthenticationService } from '../../../auth/service/app.auth.authentication.service';

// -----

@Component ({
  selector: 'app-evaluation-new',
  templateUrl: '../html/app.evaluation.new.component.html',
  animations: [routerAnimation],
  host: { '[@routerAnimation]': '' }
})
export class AppEvaluationNewComponent implements OnInit {

  newEvaluation: Evaluation;
  dirty: boolean;

  constructor(
    private authService: AppAuthAuthenticationService
  ) {
    console.log('__CONSTRUCT__ app.evaluation.new.component');
    this.authService.checkRole(['teacher'], true);
  }

  ngOnInit(): void {
    this.newEvaluation = new Evaluation();
  }

  canDeactivate(): boolean {
    if (this.dirty) {
      return confirm('Êtes-vous sûr de quitter le formulaire sans enregistrer vos modifications?');
    }
    return true;
  }

}

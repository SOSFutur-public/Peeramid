import { Component, EventEmitter, Input, OnInit, Output } from '@angular/core';

// Animations
import { slideInOutAnimation } from '../../../../animations/slide.animations';

// Services
import { AppAuthAuthenticationService } from '../../../auth/service/app.auth.authentication.service';

// -----

@Component ({
  selector: 'app-evaluation-correction-attributions',
  templateUrl: '../html/app.evaluation.correction.attributions.component.html',
  animations: [slideInOutAnimation],
  host: { '[@slideInOutAnimation]': '' }
})
export class AppEvaluationCorrectionAttributionsComponent implements OnInit {

  @Input() attributions: any;
  @Input() individualAssignment: boolean;
  @Input() individualCorrection: boolean;
  @Input() getViewAttributions: Boolean;
  @Output() getViewAttributionsChange = new EventEmitter();

  constructor(
    private authService: AppAuthAuthenticationService
  ) {
    console.log('__CONSTRUCT__ app.evaluation.correction.attributions.component');
    this.authService.checkRole(['teacher'], true);
  }

  ngOnInit(): void {
    console.log(this.individualAssignment);
    console.log(this.individualCorrection);
    console.log(this.attributions);
  }

  cancel(): void {
    this.getViewAttributions = false;
    this.getViewAttributionsChange.emit(false);
  }

}

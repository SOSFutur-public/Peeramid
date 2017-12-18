import { Component, EventEmitter, Input, OnInit, Output } from '@angular/core';

// Environment
import { environment } from '../../../../environments/environment';

// Classes
import { Correction } from '../../class/app.evaluation.class';

// Animations
import { slideInOutAnimation } from '../../../../animations/slide.animations';

// Services
import { AppAuthAuthenticationService } from '../../../auth/service/app.auth.authentication.service';

// -----

@Component ({
  selector: 'app-correction-instructions',
  templateUrl: '../html/app.assignment.correction.instructions.component.html',
  animations: [slideInOutAnimation],
  host: { '[@slideInOutAnimation]': '' }
})
export class AppAssignmentCorrectionInstructionsComponent implements OnInit {

  @Input() correction: Correction;
  @Input() getViewInstructions: Boolean;
  @Output() getViewInstructionsChange = new EventEmitter();
  // ENV
  environment = environment;

  constructor(
    private authService: AppAuthAuthenticationService,
  ) {
    console.log('__CONSTRUCT__ app.assignment.correction.instructions.component');
    this.authService.checkRole(['student', 'teacher'], true);
  }

  ngOnInit(): void {}

  cancel(): void {
    this.getViewInstructions = false;
    this.getViewInstructionsChange.emit(false);
  }

}

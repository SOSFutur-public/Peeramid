import { Injectable } from '@angular/core';

// Classes
import { Evaluation } from '../../class/app.evaluation.class';

// -----

@Injectable ()
export class AppEvaluationService {

  getEvaluationStatus(evaluation: Evaluation): string {
    let currentDate: Date;

    if (evaluation.archived) {
      return 'archived';
    }
    currentDate = new Date();
    if (evaluation.active_assignment) {
      if (evaluation.date_start_assignment < currentDate) {
        if (evaluation.active_correction && evaluation.date_end_correction < currentDate) {
          return 'finished';
        }
        return 'in-progress';
      }
      return 'incoming';
    }
    return 'draft';
  }

}

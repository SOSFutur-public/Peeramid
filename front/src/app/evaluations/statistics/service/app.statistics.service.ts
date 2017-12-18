import { Injectable } from '@angular/core';

// Classes
import { StatisticsState } from '../class/app.statistics.class';
import { Evaluation } from '../../class/app.evaluation.class';

// -----

@Injectable ()
export class AppStatisticsService {

  getState(evaluation: Evaluation): StatisticsState {
    let currentDate: Date;
    let state: StatisticsState;

    currentDate = new Date();
    state = {
      assignment: 'en cours',
      correction: 'en cours',
      opinion: 'en cours'
    };

    if (!evaluation.active_assignment) {
      state.assignment = 'désactivée';
    } else
    if (evaluation.date_start_assignment > currentDate) {
      state.assignment = 'non commencée';
    } else
    if (evaluation.date_end_assignment < currentDate) {
      state.assignment = 'terminée';
    }

    if (!evaluation.active_correction) {
      state.correction = 'désactivée';
    } else
    if (!evaluation.date_end_correction || evaluation.date_start_correction > currentDate) {
      state.correction = 'non commencée';
    } else
    if (evaluation.date_end_correction < currentDate) {
      state.correction = 'terminée';
    }

    if (!evaluation.active_correction) {
      state.opinion = 'désactivée';
    } else
    if (!evaluation.date_end_opinion || evaluation.date_end_correction > currentDate) {
      state.opinion = 'non commencée';
    } else
    if (evaluation.date_end_opinion < currentDate) {
      state.opinion = 'terminée';
    }

    return state;
  }

}

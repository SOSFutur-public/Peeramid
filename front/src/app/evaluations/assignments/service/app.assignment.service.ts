import { Injectable } from '@angular/core';

// Classes
import { Assignment, Correction, SummaryAssets } from '../../class/app.evaluation.class';

// -----

@Injectable ()
export class AppAssignmentService {

  defineAssignmentAssets(assignment: Assignment): SummaryAssets {
    let assets: SummaryAssets;

    assets = new SummaryAssets();
    assets.finished = assignment.isFinished();
    if (assignment.isFinished()) {
      assets.icon = 'fa-search';
      assets.action = 'Voir le devoir';
    } else {
      assets.icon = 'fa-pencil-square-o';
      if (assignment.date_submission) {
        assets.action = 'Modifier le devoir';
      } else {
        assets.action = 'Commencer le devoir';
      }
    }
    return assets;
  }

  defineOpinionAssets(assignment: Assignment): SummaryAssets {
    let assets: SummaryAssets;

    assets = new SummaryAssets();
    assets.finished = assignment.isOpinionFinished();
    if (assignment.isOpinionFinished()) {
      assets.icon = 'fa-search';
      assets.action = 'Voir les corrections';
    } else {
      assets.icon = 'fa-pencil-square-o';
      assets.action = 'Voir les corrections et donner son opinion';
    }
    return assets;
  }

  defineCorrectionAssets(correction: Correction): SummaryAssets {
    let assets: SummaryAssets;

    assets = new SummaryAssets();
    assets.finished = correction.isFinished();
    if (correction.isFinished()) {
      assets.icon = 'fa-search';
      assets.action = 'Voir la correction';
    } else {
      assets.icon = 'fa-pencil-square-o';
      if (correction.date_submission) {
        assets.action = 'Modifier la correction';
      } else {
        assets.action = 'Commencer la correction';
      }
    }
    if (!correction.assignment.evaluation.anonymity) {
      if (correction.assignment.evaluation.individual_assignment) {
        assets.author = `${correction.assignment.user.first_name} ${correction.assignment.user.last_name}`;
      } else {
        assets.author = correction.assignment.group.name;
      }
    }
    return assets;
  }

}

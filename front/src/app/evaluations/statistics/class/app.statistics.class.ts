import { isUndefined } from 'ngx-bootstrap/bs-moment/utils/type-checks';

// -----

export class StatisticsState {

  assignment: string = null;
  correction: string = null;
  opinion: string = null;

  constructor(data?: any) {
    if (!isUndefined(data)) {
      this.assignment = data['assignment'];
      this.correction = data['correction'];
      this.opinion = data['opinion'];
    }
  }

}

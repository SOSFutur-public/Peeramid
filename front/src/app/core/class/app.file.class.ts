import { isUndefined } from 'ngx-bootstrap/bs-moment/utils/type-checks';

export class FileType {
  id: number = null;
  type: string = null;

  constructor(data?: any) {
    if (!isUndefined(data)) {
      this.id = data['id'];
      this.type = data['type'];
    }
  }

}

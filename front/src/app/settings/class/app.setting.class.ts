import { isUndefined } from 'ngx-bootstrap/bs-moment/utils/type-checks';

// -----

export class Setting {

  id: number;
  type: string;
  name: string;
  value: string;

  constructor(data?: any) {
    if (!isUndefined(data)) {
      this.id = data['id'];
      this.type = data['type'];
      this.name = data['name'];
      this.value = data['value'];
    }
  }

}

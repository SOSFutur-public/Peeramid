import { Component, Input } from '@angular/core';
import { FormControl } from '@angular/forms';
import {isUndefined} from "ngx-bootstrap/bs-moment/utils/type-checks";

// -----

@Component({
  selector: 'app-core-form-errors',
  templateUrl: '../html/app.core.form.errors.component.html',
  styleUrls: [],
})
export class AppCoreFormErrorsComponent {

  @Input() controls: FormControl[];
  @Input() frontCheck;
  @Input() backCheck;
  @Input() always: boolean;

  constructor() {
    console.log('__CONSTRUCT__ app.core.form.errors.component');
  }

  front(): boolean {
    if (!isUndefined(this.always) && this.always) {
      return true;
    }
    if (this.frontCheck && this.frontCheck.length > 0) {
      for (const control of this.controls) {
        if (control.dirty || control.touched) {
          return true;
        }
      }
    }
    return false;
  }
  back(): boolean {
    if (this.backCheck && this.backCheck.length > 0) {
      for (const control of this.controls) {
        if (control.dirty) {
          return false;
        }
      }
    }
    return true;
  }

}

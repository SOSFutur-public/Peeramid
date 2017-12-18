import { AbstractControl, ValidatorFn } from '@angular/forms';

// -----

export function MaximumValueValidator(max: number): ValidatorFn {
  return (control: AbstractControl): { [key: string]: any } => {
    let value: number;

    value = parseInt(control.value, 10);
    return value <= max ? null : { 'MaximumValueExceeded': value };
  };
}

import { AbstractControl, ValidatorFn } from '@angular/forms';

// -----

export function StepValidator(step: number): ValidatorFn {
  return (control: AbstractControl): { [key: string]: any } => {
    let value: number;
    let stepTmp: number;
    let factor: number;
    let result: boolean;

    if (!control.value) {
      return null;
    }
    value = parseFloat(control.value);
    factor = (`${value}`.split('.')[1] || []).length;
    factor = Math.max(factor,  (`${step}`.split('.')[1] || []).length);
    value *= Math.pow(10, factor);
    stepTmp = step * Math.pow(10, factor);
    result = value % stepTmp === 0;
    return result ? null : { 'UnmatchingStep': value };
  };
}

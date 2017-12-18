import { AbstractControl, ValidatorFn } from '@angular/forms';
import { isUndefined } from 'ngx-bootstrap/bs-moment/utils/type-checks';

// -----

export function LenMinValidator(min: number, avoid?: boolean): ValidatorFn {
  return (control: AbstractControl): { [key: string]: any } => {
    let array: any[];
    let result: boolean;

    avoid = (isUndefined(avoid) ? false : avoid);
    if (avoid) {
      return null;
    }
    result = false;
    array = control.value;
    if (Array.isArray(array)) {
      result = array.length >= min;
    }
    return result ? null : { 'LowerValue': min };
  };
}
export function LenMaxValidator(max: number, avoid?: boolean): ValidatorFn {
  return (control: AbstractControl): { [key: string]: any } => {
    let array: any[];
    let result: boolean;

    avoid = (isUndefined(avoid) ? false : avoid);
    if (avoid) {
      return null;
    }
    result = false;
    array = control.value;
    if (Array.isArray(array)) {
      result = array.length >= max;
    }
    return result ? null : { 'LowerValue': max };
  };
}

export function checkMin(min: number, value: any): boolean {
  if (value === null) {
    return true;
  }
  return value >= min;
}
export function checkMax(max: number, value: any): boolean {
  if (value === null) {
    return true;
  }
  return value <= max;
}
export function checkInteger(value: any): boolean {
  if (value === null) {
    return true;
  }
  return value === parseInt(value, 10);
}
export function checkMinNumberOfValues(min: number, values: [any]): boolean {
  if (values === null) {
    return false;
  } else if (values[0] instanceof Array) {
    for (const array of values) {
      if (array.length < min) {
        return false;
      }
    }
  }
  return values.length >= min;
}
export function checkMaxNumberOfValues(max: number, values: [any]): boolean {
  if (values === null) {
    return true;
  }
  return values.length <= max;
}

export function checkRegex(regex: string, value): boolean {
  if (value === null || value === '') {
    return true;
  }
  const reg = new RegExp(regex);
  return reg.test(value);
}
export function checkSame(same: string, value): boolean {
  if (value === null || value === '') {
    return true;
  }
  return same === value;
}

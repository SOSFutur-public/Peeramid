import { AbstractControl, ValidatorFn } from '@angular/forms';
import { isUndefined } from 'ngx-bootstrap/bs-moment/utils/type-checks';

// -----

export function DateRangeValidator(): ValidatorFn {
  return (control: AbstractControl): { [key: string]: any } => {
    let dateTimeStart: Date;
    let dateTimeEnd: Date;
    let validRange: boolean;

    validRange = true;
    if (control.get('dateStart').value && control.get('timeStart').value
      && control.get('dateEnd').value && control.get('timeEnd').value) {
      dateTimeStart = new Date(
        control.get('dateStart').value.getFullYear(),
        control.get('dateStart').value.getMonth(),
        control.get('dateStart').value.getDate(),
        control.get('timeStart').value.getHours(),
        control.get('timeStart').value.getMinutes());
      dateTimeEnd = new Date(
        control.get('dateEnd').value.getFullYear(),
        control.get('dateEnd').value.getMonth(),
        control.get('dateEnd').value.getDate(),
        control.get('timeEnd').value.getHours(),
        control.get('timeEnd').value.getMinutes());
      validRange = dateTimeStart < dateTimeEnd;
    }
    return validRange ? null : { 'InvalidDateRange': true };
  };
}

export function DateMaxValidator(dateMax: AbstractControl, timeMax: AbstractControl, moment: string): ValidatorFn {
  return (control: AbstractControl): { [key: string]: any } => {
    let dateTime: Date;
    let dateTimeMax: Date;
    let dateControl: string;
    let timeControl: string;
    let isLower: boolean;

    isLower = true;
    dateControl = 'date' + moment;
    timeControl = 'time' + moment;
    if (control.get(dateControl).value && control.get(timeControl).value && dateMax.value && timeMax.value) {
      dateTime = new Date(
        control.get(dateControl).value.getFullYear(),
        control.get(dateControl).value.getMonth(),
        control.get(dateControl).value.getDate(),
        control.get(timeControl).value.getHours(),
        control.get(timeControl).value.getMinutes());
      dateTimeMax = new Date(
        dateMax.value.getFullYear(),
        dateMax.value.getMonth(),
        dateMax.value.getDate(),
        timeMax.value.getHours(),
        timeMax.value.getMinutes());
      isLower = dateTime < dateTimeMax;
    }
    return isLower ? null : { 'HigherThanMaximumDate': true };
  };
}

export function DateMinValidator(dateMin: AbstractControl, timeMin: AbstractControl, moment: string): ValidatorFn {
  return (control: AbstractControl): { [key: string]: any } => {
    let dateTime: Date;
    let dateTimeMin: Date;
    let dateControl: string;
    let timeControl: string;
    let isHigher: boolean;

    isHigher = true;
    dateControl = 'date' + moment;
    timeControl = 'time' + moment;
    if (control.get(dateControl).value && control.get(timeControl).value && dateMin.value && timeMin.value) {
        dateTime = new Date(
        control.get(dateControl).value.getFullYear(),
        control.get(dateControl).value.getMonth(),
        control.get(dateControl).value.getDate(),
        control.get(timeControl).value.getHours(),
        control.get(timeControl).value.getMinutes());
      dateTimeMin = new Date(
        dateMin.value.getFullYear(),
        dateMin.value.getMonth(),
        dateMin.value.getDate(),
        timeMin.value.getHours(),
        timeMin.value.getMinutes());
      isHigher = dateTime > dateTimeMin;
    }
    return isHigher ? null : { 'LowerThanMinimumDate': true };
  };
}

export function checkMinDate(dates): boolean {
  if (isUndefined(dates[0]) || dates[0] === null) {
    return true;
  }
  if (isUndefined(dates[1]) || dates[1] === null) {
    return true;
  }
  const date = new Date(
    dates[0].getFullYear(),
    dates[0].getMonth(),
    dates[0].getDate(),
    dates[0].getHours(),
    dates[0].getMinutes());
  const date_min = new Date(
    dates[1].getFullYear(),
    dates[1].getMonth(),
    dates[1].getDate(),
    dates[1].getHours(),
    dates[1].getMinutes());
  return date > date_min;
}
export function checkMaxDate(dates): boolean {
  if (isUndefined(dates[0]) || dates[0] === null) {
    return true;
  }
  if (isUndefined(dates[1]) || dates[1] === null) {
    return true;
  }
  const date = new Date(
    dates[0].getFullYear(),
    dates[0].getMonth(),
    dates[0].getDate(),
    dates[0].getHours(),
    dates[0].getMinutes());
  const date_min = new Date(
    dates[1].getFullYear(),
    dates[1].getMonth(),
    dates[1].getDate(),
    dates[1].getHours(),
    dates[1].getMinutes());
  return date < date_min;
}

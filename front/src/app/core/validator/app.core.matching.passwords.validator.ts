import { AbstractControl, ValidatorFn } from '@angular/forms';

// Functions
import { checkStrongPassword } from '../functions/app.core.utils.functions';

// -----

export function MatchingPasswordsValidator(): ValidatorFn {
  return (control: AbstractControl): { [key: string]: any } => {
    let match: boolean;

    match = control.get('password').value == control.get('passwordConfirm').value;
    return match ? null : { 'UnmatchingPasswords': true };
  };
}

export function StrongPasswordValidator(): ValidatorFn {
  return (control: AbstractControl): { [key: string]: any } => {
    return checkStrongPassword(control.value) || control.value == '' ? null : { 'NotStrongPassword': true };
  };
}

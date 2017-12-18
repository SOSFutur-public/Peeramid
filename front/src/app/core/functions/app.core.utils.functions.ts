import { isUndefined } from 'ngx-bootstrap/bs-moment/utils/type-checks';

// Environment
import { environment } from '../../../environments/environment';

// -----

export function getDate(date: any): Date {
  if (isUndefined(date) || date === null) {
    return null;
  }
  if (Object.prototype.toString.call(date) === '[object Date]') {
    return date;
  }
  return new Date(Number(date.substr(0, 4)), Number(date.substr(5, 2)) - 1, Number(date.substr(8, 2)), Number(date.substr(11, 2)), Number(date.substr(14, 2)), Number(date.substr(17, 2)));
}

export function joinDateAndTime(date, time) {
  if (date !== null && time !== null) {
    return new Date(
      date.getFullYear(),
      date.getMonth(),
      date.getDate(),
      time.getHours(),
      time.getMinutes());
  }
  return null;
}

export function random(n): Number {
  return  Math.floor(Math.random() * n) + 1;
}

export function shuffle(array): String[] {
  const a = array;
  for (let i = array.length; i; i--) {
    const j = Math.floor(Math.random() * i);
    [a[i - 1], a[j]] = [a[j], a[i - 1]];
  }
  return a;
}

export function checkStrongPassword(password): boolean {
  const strongRegex = new RegExp('^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[,;.:+=_\?!@#\$%\^&\*-])(?=.{8,})');
  return strongRegex.test(password);
}

export function displayDefaultImage(path = '') {
  return environment.upload_url + path + '/default.png';
}

export function imgExists(src) {
  const img = new Image();
  img.src = src;
  return img.height !== 0;
}

export function joinKeyFromAssociativeArrays(arrays, key) {
  let response = '';
  for (const array of arrays) {
    response += array[key] + ', ';
  }
  return response.slice(0, -2);
}

export function snakeCaseToCamelCase(string: string) {
  const array = string.split('_');
  let result = array[0];
  for (let i = 1; i < array.length; i++) {
    result += array[i].charAt(0).toUpperCase() + array[i].substr(1);
  }
  return result;
}

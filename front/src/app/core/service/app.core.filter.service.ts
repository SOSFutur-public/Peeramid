import { Injectable } from '@angular/core';
import { isNullOrUndefined } from 'util';

// -----

@Injectable ()
export class AppCoreFilterService {

  matchingStrings(str1: string, str2: string): boolean {
    return (str1.toLowerCase().indexOf(str2.toLowerCase()) >= 0
      || str2.toLowerCase().indexOf(str1.toLowerCase()) >= 0);
  }

  handleMultipleProperties(object: any, property: string): any {
    let properties: string[];
    let len: number;

    properties = property.split('.');
    len = properties.length;
    for (let i = 0; i < len; i++) {
      if (isNullOrUndefined(object[properties[i]])) {
        return null;
      }
      object = object[properties[i]];
    }
    return object;
  }

  sortConditions(property: string, ascendOrder?: boolean): any {
    return (a: any, b: any) => {
      let res: number;

      a = this.handleMultipleProperties(a, property);
      b = this.handleMultipleProperties(b, property);
      if (typeof a === 'number') {
        res = ((a < b) ? -1 : (a > b) ? 1 : 0);
      } else {
        res = (a.toLowerCase() > b.toLowerCase()) ? 1 : 0;
        res = (a.toLowerCase() < b.toLowerCase()) ? -1 : res;
      }
      if (!ascendOrder) {
        res *= -1;
      }
      return res;
    };
  }

  sortList(list: any[], property: string, ascendOrder?: boolean): any[] {
    return list.sort(this.sortConditions(property, ascendOrder));
  }

}

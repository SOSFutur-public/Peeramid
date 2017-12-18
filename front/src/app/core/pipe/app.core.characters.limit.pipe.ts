import { PipeTransform, Pipe } from '@angular/core';

// -----

@Pipe({
  name: 'limitTo'
})
export class AppCoreCharactersLimitPipe implements PipeTransform {

  transform(value: string, limit: number): string {
    return value.length > limit ? value.substring(0, limit) + '...' : value;
  }

}

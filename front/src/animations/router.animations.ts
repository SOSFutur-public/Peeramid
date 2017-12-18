import { trigger, state, animate, style, transition } from '@angular/animations';

export const routerAnimation =
  trigger('routerAnimation', [
    transition(':enter', [
      style({ opacity : 0 }),
      animate('.5s ease-in-out', style({ opacity : 1 }))
    ]),
    /*transition(':leave', [
      style({ opacity : 0 }),
    ])*/
  ]);

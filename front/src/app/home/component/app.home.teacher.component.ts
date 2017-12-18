import { Component, OnInit } from '@angular/core';

// Animations
import { routerAnimation } from '../../../animations/router.animations';

// Services
import { AppAuthAuthenticationService } from '../../auth/service/app.auth.authentication.service';

// -----

@Component ({
  selector: 'app-home-teacher',
  templateUrl: '../html/app.home.teacher.component.html',
  animations: [routerAnimation],
  host: {'[@routerAnimation]': ''}
})
export class AppHomeTeacherComponent implements OnInit {

  constructor(
    private authService: AppAuthAuthenticationService,
  ) {
    console.log('__CONSTRUCT__ app.home.teacher.component');
    this.authService.checkRole(['teacher'], true);
  }

  ngOnInit(): void {
  }

}

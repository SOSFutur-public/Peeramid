import { Component, Input } from '@angular/core';

// Classes
import { Lesson } from '../class/app.lesson.class';

// Services
import { AppAuthAuthenticationService } from '../../auth/service/app.auth.authentication.service';


// -----
@Component ({
  selector: 'app-lesson-summary',
  templateUrl: '../html/app.lesson.summary.component.html',
})
export class AppLessonSummaryComponent {

  @Input() lesson: Lesson;

  constructor(
    private authService: AppAuthAuthenticationService
  ) {
    console.log('__CONSTRUCT__ app.lesson.summary.component');
    this.authService.checkRole(['student', 'teacher'], true);
  }

}

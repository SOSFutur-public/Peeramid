import { Component, OnInit } from '@angular/core';

// Classes
import { Lesson } from '../class/app.lesson.class';

// Animations
import { routerAnimation } from '../../../animations/router.animations';

// Services
import { AppAuthAuthenticationService } from '../../auth/service/app.auth.authentication.service';

// -----

@Component ({
  selector: 'app-lesson-new',
  templateUrl: '../html/app.lesson.new.component.html',
  animations: [routerAnimation],
  host: { '[@routerAnimation]': '' }
})
export class AppLessonNewComponent implements OnInit {

  newLesson: Lesson;
  dirty: boolean;

  constructor(
    private authService: AppAuthAuthenticationService
  ) {
    this.authService.checkRole(['admin'], true);
  }

  ngOnInit(): void {
    this.newLesson = new Lesson();
  }

  canDeactivate(): boolean {
    if (this.dirty) {
      return confirm('Êtes-vous sûr de quitter le formulaire sans enregistrer vos modifications?');
    }
    return true;
  }

}

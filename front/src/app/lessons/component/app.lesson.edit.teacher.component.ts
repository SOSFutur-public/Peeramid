import { Component, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';

// Classes
import { Lesson } from '../class/app.lesson.class';

// Animations
import { routerAnimation } from '../../../animations/router.animations';

// Services
import { AppCoreRestService } from '../../core/service/app.core.rest.service';
import { AppAuthAuthenticationService } from '../../auth/service/app.auth.authentication.service';
import { AppCoreLoaderService } from '../../core/service/app.core.loader.service';

// -----

@Component ({
  selector: 'app-lesson-edit-teacher',
  templateUrl: '../html/app.lesson.edit.teacher.component.html',
  animations: [routerAnimation],
  host: { '[@routerAnimation]': '' }
})
export class AppLessonEditTeacherComponent implements OnInit {

  editLesson: Lesson;
  dirty: boolean;

  constructor(
    private route: ActivatedRoute,
    private loaderService: AppCoreLoaderService,
    private restService: AppCoreRestService,
    private authService: AppAuthAuthenticationService
  ) {
    console.log('__CONSTRUCT__ app.lesson.edit.component');
    this.authService.checkRole(['teacher'], true);
  }

  ngOnInit(): void {
    let id: number;

    id = +this.route.snapshot.params['id'];
    this.getLesson(id);
  }

  canDeactivate(): boolean {
    if (this.dirty) {
      return confirm('Êtes-vous sûr de quitter le formulaire sans enregistrer vos modifications?');
    }
    return true;
  }

  getLesson(id: number): void {
    this.loaderService.display(true);
    this.restService.getDb('lessons', [id])
      .then(lessons => {
        this.editLesson = new Lesson(lessons);
        this.loaderService.display(false);
      });
  }

}

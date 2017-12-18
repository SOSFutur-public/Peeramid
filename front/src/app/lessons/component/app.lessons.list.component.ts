import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';

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
  selector: 'app-lessons-student',
  templateUrl: '../html/app.lessons.list.component.html',
  animations: [routerAnimation],
  host: { '[@routerAnimation]': '' }
})
export class AppLessonsListComponent implements OnInit {

  lessons: Lesson[];

  constructor(
    private authService: AppAuthAuthenticationService,
    private loaderService: AppCoreLoaderService,
    private restService: AppCoreRestService,
    private router: Router
  ) {
    console.log('__CONSTRUCT__ app.lessons.student.component');
    this.authService.checkRole(['student', 'teacher'], true);
  }

  ngOnInit() {
    this.getLessons();
  }

  getLessons(): void {
    this.loaderService.display(true);
    this.lessons = [];
    this.restService.getDb('userLessons')
      .then(lessons => {
        lessons.forEach(lesson => {
          this.lessons.push(new Lesson(lesson));
        });
        this.loaderService.display(false);
      });
  }

  toLesson(id) {
    let path: string[];
    let roleId: number;

    roleId = this.authService.user.role.id;
    if (roleId === 2) {
      path = ['/student/lesson/', id];
    } else
    if (roleId === 3) {
      path = ['/teacher/lesson/', id];
    }
    this.router.navigate(path);
  }

}

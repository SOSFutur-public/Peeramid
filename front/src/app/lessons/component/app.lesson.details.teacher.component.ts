import { Component, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';

// Environment
import { environment } from '../../../environments/environment';

// Animations
import { routerAnimation } from '../../../animations/router.animations';

// Classes
import { Lesson } from '../class/app.lesson.class';
import { User } from '../../users/class/app.user.class';

// Services
import { AppCoreRestService } from '../../core/service/app.core.rest.service';
import { AppAuthAuthenticationService } from '../../auth/service/app.auth.authentication.service';
import { AppCoreLoaderService } from '../../core/service/app.core.loader.service';

// -----

@Component ({
  selector: 'app-lesson-details-teacher',
  templateUrl: '../html/app.lesson.details.teacher.component.html',
  animations: [routerAnimation],
  host: { '[@routerAnimation]': '' }
})
export class AppLessonDetailsTeacherComponent implements OnInit {

  // ENV
  environment = environment;
  lesson: Lesson;
  students: User[];
  teachers: User[];

  constructor(
    private route: ActivatedRoute,
    private loaderService: AppCoreLoaderService,
    private restService: AppCoreRestService,
    private authService: AppAuthAuthenticationService
  ) {
    console.log('__CONSTRUCT__ app.lesson.details.teacher.component');
    this.authService.checkRole(['teacher'], true);
  }

  ngOnInit() {
    this.getLesson(this.route.snapshot.params['id']);
  }

  getLesson(id: number): void {
    this.loaderService.display(true);
    this.teachers = [];
    this.students = [];
    this.restService.getDb('lessonTeacher', [id])
      .then(lesson => this.lesson = new Lesson(lesson))
      .then(() => {
        this.lesson.getTeachers().forEach(teacher => {
          this.teachers.push(new User(teacher));
        });
        this.lesson.getStudents().forEach(student => {
          this.students.push(new User(student));
        });
        this.loaderService.display(false);
      });
  }

}

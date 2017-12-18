import { Component, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';

// Classes
import { Lesson } from '../class/app.lesson.class';
import { User } from '../../users/class/app.user.class';
import { Assignment, Correction } from '../../evaluations/class/app.evaluation.class';

// Animations
import { routerAnimation } from '../../../animations/router.animations';

// Services
import { AppCoreRestService } from '../../core/service/app.core.rest.service';
import { AppAuthAuthenticationService } from '../../auth/service/app.auth.authentication.service';
import { AppCoreLoaderService } from '../../core/service/app.core.loader.service';

// -----

@Component ({
  selector: 'app-lesson-details-student',
  templateUrl: '../html/app.lesson.details.student.component.html',
  styleUrls: ['../../../assets/css/app.lesson.details.student.component.scss'],
  animations: [routerAnimation],
  host: { '[@routerAnimation]': '' }
})
export class AppLessonDetailsStudentComponent implements OnInit {

  lesson: Lesson;
  teachers: User[];
  assignments: { individual_assignments: Assignment[], group_assignments: Assignment[] };
  corrections: { individual_corrections: Correction[], group_corrections: Correction[] };

  constructor(
    private route: ActivatedRoute,
    private loaderService: AppCoreLoaderService,
    private restService: AppCoreRestService,
    private authService: AppAuthAuthenticationService
  ) {
    console.log('__CONSTRUCT__ app.lesson.summary.component');
    this.authService.checkRole(['student'], true);
  }

  ngOnInit() {
    let lessonId: number;

    lessonId = this.route.snapshot.params['id'];
    this.getLesson(lessonId);
    this.getAssignments(lessonId);
    this.getCorrections(lessonId);
  }

  getLesson(id: number): void {
    this.loaderService.display(true);
    this.teachers = [];
    this.restService.getDb('lessons', [id])
      .then(lesson => this.lesson = new Lesson(lesson))
      .then (() => {
        this.lesson.getTeachers().forEach(teacher => {
          this.teachers.push(new User(teacher));
        });
        this.loaderService.display(false);
      });
  }

  getAssignments(lessonId: number): void {
    this.loaderService.display(true);
    this.assignments = {
      individual_assignments: [],
      group_assignments: []
    };
    this.restService.getDb('lessonAssignments', [lessonId])
      .then(assignments => {
        assignments.individual_assignments.forEach(individual_assignment => {
          this.assignments.individual_assignments.push(new Assignment(individual_assignment));
        });
        assignments.group_assignments.forEach(group_assignment => {
          this.assignments.group_assignments.push(new Assignment(group_assignment));
        });
        this.loaderService.display(false);
      });
  }

  getCorrections(lessonId: number): void {
    this.corrections = {
      individual_corrections: [],
      group_corrections: []
    };
    this.restService.getDb('lessonCorrections', [lessonId])
      .then(corrections => {
        corrections.individual_corrections.forEach(individual_correction => {
          this.corrections.individual_corrections.push(new Correction(individual_correction));
        });
        corrections.group_corrections.forEach(group_correction => {
          this.corrections.group_corrections.push(new Correction(group_correction));
        });
      });
  }

}

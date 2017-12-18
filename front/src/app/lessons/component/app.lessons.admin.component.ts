import { Component, OnInit, Input } from '@angular/core';

// Classes
import { Lesson } from '../class/app.lesson.class';

// Animations
import { routerAnimation } from '../../../animations/router.animations';

// Services
import { AppCoreFilterService } from '../../core/service/app.core.filter.service';
import { AppCoreRestService } from '../../core/service/app.core.rest.service';
import { AppAuthAuthenticationService } from '../../auth/service/app.auth.authentication.service';
import { AppCoreAlertService } from '../../core/service/app.core.alert.service';
import { AppCoreLoaderService } from '../../core/service/app.core.loader.service';

// -----

@Component ({
  selector: 'app-lessons-admin',
  templateUrl: '../html/app.lessons.admin.component.html',
  animations: [routerAnimation],
  host: { '[@routerAnimation]': '' }
})
export class AppLessonsAdminComponent implements OnInit {

  @Input() searchBox: string;
  lessons: Lesson[] = [];
  searchLabel: { name: string, property: string };
  sortParams: { column: string, ascendOrder: boolean };

  constructor(
    private authService: AppAuthAuthenticationService,
    private loaderService: AppCoreLoaderService,
    private restService: AppCoreRestService,
    private alertService: AppCoreAlertService,
    private filterService: AppCoreFilterService
  ) {
    console.log('__CONSTRUCT__ app.lessons.admin.component');
    this.authService.checkRole(['admin'], true);
  }

  ngOnInit(): void {
    this.searchBox = '';
    this.searchLabel = { name: 'Intitulé', property: 'name' };
    this.sortParams = { column: null, ascendOrder: true };
    this.getLessons();
  }

  getLessons(): void {
    this.loaderService.display(true);
    this.restService.getDb('lessons')
      .then(lessons => {
        for (const lesson of lessons) {
          this.lessons.push(new Lesson(lesson));
        }
      })
      .then(() => {
        console.log(this.lessons);
        this.loaderService.display(false);
      });
  }

  isSelected(column: string, ascendOrder: boolean) {
    if (column === this.sortParams.column && ascendOrder === this.sortParams.ascendOrder) {
      return ' active';
    }
    return '';
  }

  sortLessons(property: string, column: string, ascendOrder: boolean): void {
    this.sortParams.ascendOrder = ascendOrder;
    this.sortParams.column = column;
    this.filterService.sortList(this.lessons, property, this.sortParams.ascendOrder);
  }

  matchingSearch(object: any, propriety: string) {
    let str: string;

    str = this.filterService.handleMultipleProperties(object, propriety);
    return this.filterService.matchingStrings(str, this.searchBox);
  }

  deleteLesson(lesson: Lesson): void {
    let index: number;

    if (confirm(`Etes vous sûr de vouloir supprimer le cours \'${lesson.name}\'?`)) {
      this.loaderService.display(true);
      this.restService.deleteDb('lessons', [lesson.id])
        .then(() => {
          index = this.lessons.indexOf(lesson);
          this.lessons.splice(index, 1);
          this.alertService.setAlert(`Le cours \'${lesson.name}\' a bien été supprimé`);
          this.loaderService.display(false);
        })
        .catch(() => {
          console.error(`Lesson \'${lesson.name}\' cannot be deleted in the database.`);
          this.alertService.setAlert('Une erreur est survenue...', 'error');
          this.loaderService.display(false);
        });
    }
  }

}

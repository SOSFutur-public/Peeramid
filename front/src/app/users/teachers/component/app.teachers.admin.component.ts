import { Component, OnInit, Input } from '@angular/core';

// Classes
import { User } from '../../class/app.user.class';

// Animations
import { routerAnimation } from '../../../../animations/router.animations';

// Services
import { AppCoreRestService } from '../../../core/service/app.core.rest.service';
import { AppCoreFilterService } from '../../../core/service/app.core.filter.service';
import { AppCoreAlertService } from '../../../core/service/app.core.alert.service';
import { AppAuthAuthenticationService } from '../../../auth/service/app.auth.authentication.service';
import { AppCoreLoaderService } from '../../../core/service/app.core.loader.service';

// -----

@Component ({
  selector: 'app-teachers-admin',
  templateUrl: '../html/app.teachers.admin.component.html',
  animations: [routerAnimation],
  host: { '[@routerAnimation]': '' }
})
export class AppTeachersAdminComponent implements OnInit {

  @Input() searchBox: string;
  searchLabel: { name: string, property: string };
  teachers: User[];
  sortParams: { column: string, ascendOrder: boolean };


  constructor(
    private restService: AppCoreRestService,
    private authService: AppAuthAuthenticationService,
    private loaderService: AppCoreLoaderService,
    private filterService: AppCoreFilterService,
    private alertService: AppCoreAlertService
  ) {
    this.authService.checkRole(['admin'], true);
  }

  ngOnInit(): void {
    this.searchBox = '';
    this.searchLabel = { name: 'Nom', property: 'last_name' };
    this.sortParams = { column: null, ascendOrder: true };
    this.getTeachers();
  }

  getTeachers(): void {
    this.loaderService.display(true);
    this.restService.getDb('teachers')
      .then(teachers => this.teachers = teachers)
      .then(() => this.loaderService.display(false));
  }

  isSelected(column: string, ascendOrder: boolean) {
    if (column === this.sortParams.column && ascendOrder === this.sortParams.ascendOrder) {
      return ' active';
    }
    return '';
  }

  sortTeachers(property: string, column: string, ascendOrder: boolean): void {
    this.sortParams.ascendOrder = ascendOrder;
    this.sortParams.column = column;
    this.filterService.sortList(this.teachers, property, this.sortParams.ascendOrder);
  }

  deleteTeacher(teacher: User): void {
    let index: number;

    if (confirm(`Etes vous sûr de vouloir supprimer l'enseignant \'${teacher.username}\'?`)) {
      this.loaderService.display(true);
      this.restService.deleteDb('teacher', [teacher.id])
        .then(() => {
          index = this.teachers.indexOf(teacher);
          this.teachers.splice(index, 1);
          this.alertService.setAlert('L\'enseignant a bien été supprimé');
          this.loaderService.display(false);
        })
        .catch(() => {
          console.error(`Teacher \'${teacher.username}\', id: ${teacher.id}, cannot be deleted in the database.`);
          this.alertService.setAlert('Une erreur est survenue...', 'error');
          this.loaderService.display(false);
        });
    }
  }

  matchingSearch(object: any, propriety: string) {
    let str: string;

    str = this.filterService.handleMultipleProperties(object, propriety);
    return this.filterService.matchingStrings(str, this.searchBox);
  }

}

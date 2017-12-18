import { Component, OnInit, Input } from '@angular/core';
import { Router } from '@angular/router';

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
  selector: 'app-students-admin',
  templateUrl: '../html/app.students.admin.component.html',
  animations: [routerAnimation],
  host: { '[@routerAnimation]': '' }
})
export class AppStudentsAdminComponent implements OnInit {

  @Input() searchBox: string;
  students: User[] = [];
  searchLabel: { name: string, property: string };
  sortParams: { column: string, ascendOrder: boolean };
  // VIEWS
  view_import: Boolean = false;

  constructor(
    private restService: AppCoreRestService,
    private authService: AppAuthAuthenticationService,
    private loaderService: AppCoreLoaderService,
    private filterService: AppCoreFilterService,
    private alertService: AppCoreAlertService,
    private router: Router
  ) {
    console.log('__CONSTRUCT__ app.students.admin.component');
    this.authService.checkRole(['admin'], true);
  }

  ngOnInit(): void {
    this.searchBox = '';
    this.searchLabel = { name: 'Nom', property: 'last_name' };
    this.sortParams = { column: null, ascendOrder: true };
    this.getStudents();
  }

  getStudents(): void {
    this.loaderService.display(true);
    this.students = [];
    this.restService.getDb('students')
      .then(students => {
        for (const student of students) {
          this.students.push(new User(student));
        }
      })
      .then(() => {
        this.loaderService.display(false);
      });
  }

  isSelected(column: string, ascendOrder: boolean) {
    if (column === this.sortParams.column && ascendOrder === this.sortParams.ascendOrder) {
      return ' active';
    }
    return '';
  }

  sortStudents(property: string, column: string, ascendOrder: boolean): void {
    this.sortParams.ascendOrder = ascendOrder;
    this.sortParams.column = column;
    this.filterService.sortList(this.students, property, this.sortParams.ascendOrder);
  }

  deleteStudent(student: User): void {
    let index: number;

    if (confirm(`Etes vous sûr de vouloir supprimer l'étudiant \'${student.username}\'?`)) {
      this.loaderService.display(true);
      this.restService.deleteDb('users', [student.id])
        .then(() => {
          index = this.students.indexOf(student);
          this.students.splice(index, 1);
          this.alertService.setAlert('L\'Étudiant ' + student.name() + ' a bien été supprimé !', 'success');
          this.loaderService.display(false);
        })
        .catch(() => {
          console.error(`Student \'${student.username}\', id: ${student.id}, cannot be deleted in the database.`);
          alert(`Une erreur est survenue durant la suppression de l'étudiant \'${student.username}\'.`);
          this.loaderService.display(false);
        });
    }
  }

  matchingSearch(object: any, propriety: string) {
    let str: string;

    str = this.filterService.handleMultipleProperties(object, propriety);
    return this.filterService.matchingStrings(str, this.searchBox);
  }

  toUser(id) {
    this.router.navigate(['/admin/student/', id, 'edit']);
  }
  displayImport() {
    this.view_import = true;
  }

}

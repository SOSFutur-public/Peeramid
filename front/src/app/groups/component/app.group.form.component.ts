import {Component, EventEmitter, Input, OnInit, Output} from '@angular/core';
import { FormBuilder, FormControl, FormGroup, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { IMultiSelectSettings, IMultiSelectTexts } from 'angular-2-dropdown-multiselect';

// Environment
import { environment } from '../../../environments/environment';

// Classes
import { Group } from '../class/app.group.class';

// Animations
import { routerAnimation } from '../../../animations/router.animations';

// Services
import { AppCoreRestService } from '../../core/service/app.core.rest.service';
import { AppCoreFormService } from '../../core/service/app.core.form.service';
import { AppCoreAlertService } from '../../core/service/app.core.alert.service';
import { AppAuthAuthenticationService } from '../../auth/service/app.auth.authentication.service';
import { AppCoreLoaderService } from '../../core/service/app.core.loader.service';

// -----

@Component ({
  selector: 'app-group-form',
  templateUrl: '../html/app.group.form.component.html',
  animations: [routerAnimation],
  host: { '[@routerAnimation]': '' }
})
export class AppGroupFormComponent implements OnInit {

  @Input() group: Group;
  @Input() dirty: boolean;
  @Output() dirtyChange = new EventEmitter<boolean>();
  _group: any;
  // ENV
  environment = environment;
  // FORM
  groupForm: FormGroup;
  invalidForm: boolean;
  // CONTROL
  nameControl: FormControl;
  studentsControl: FormControl;
  lessonsControl: FormControl;
  // SELECT
  students: {}[] = [];
  lessons:  {}[] = [];
  selectSettings: IMultiSelectSettings;
  studentsSelectTexts: IMultiSelectTexts;
  lessonsSelectTexts: IMultiSelectTexts;
  // ERRORS
  backChecks = null;
  saveChecks = null;
  backControls = {};
  saveControls = {};

  constructor(
    private formBuilder: FormBuilder,
    private router: Router,
    private restService: AppCoreRestService,
    private authService: AppAuthAuthenticationService,
    private loaderService: AppCoreLoaderService,
    private formService: AppCoreFormService,
    private selectService: AppCoreFormService,
    private alertService: AppCoreAlertService,
  ) {
    console.log('__CONSTRUCT__ app.group.form.component');
    this.authService.checkRole(['admin'], true);
    setInterval(() => {
      this.dirtyChange.emit(this.groupForm.dirty);
    }, 1000);
  }

  ngOnInit(): void {
    this.invalidForm = false;
    this.selectSettings = this.selectService.multipleSelectSettings();
    // SELECT
    this.getStudents();
    this.getLessons();
    // FORM
    this.setForm();
    // CHECKS
    this.checks();
    this.checksFront(true);
    this.checksBack(true);
  }
  //CHECKS
  checks() {
    this.groupForm.valueChanges.subscribe(data => { this.checksFront(); });
  }
  checksFront(init: boolean = false) {
    // Function
    if (init) {
      this.saveControls = {
        'name':     [this.nameControl,    {
          'required' : 'Le nom du groupe est requis.'
        }, 'Nom', null],
        'students':     [this.studentsControl,    {}, 'Liste des étudiants', null],
        'lessons':     [this.lessonsControl,    {}, 'Liste des cours', null],
      };
    }
    this.saveChecks = this.formService.checkFrontErrors(this.saveControls);
  }
  checksBack(init: boolean = false, response = []) {
    if (init) {
      this.backControls = [
        'name',
        'students',
        'lessons'
      ];
    }
    this.backChecks = this.formService.checkBackErrors(this.backControls, response);
  }

  // SELECT
  getStudents(): void {
    this.loaderService.display(true);
    this.studentsSelectTexts = this.selectService.selectTexts(' des étudiants...');
    this.restService.getDb('students')
      .then(students => {
        for (const student of students) {
          this.students.push({ id: student.id, name: student.last_name + ' ' + student.first_name });
        }
        this.loaderService.display(false);
      });
  }
  getLessons(): void {
    this.loaderService.display(true);
    this.lessonsSelectTexts = this.selectService.selectTexts(' des cours...');
    this.restService.getDb('lessons')
      .then(lessons => {
        for (const lesson of lessons) {
          this.lessons.push({ id: lesson.id, name: lesson.name });
        }
        this.loaderService.display(false);
      });
  }

  // FORM
  setForm(): void {
    this.groupForm = this.formBuilder.group({
      name: this.nameControl = new FormControl(
        this.group.name,
        // Validators.required
      ),
      students: this.studentsControl = new FormControl(
        this.group.getUsersId()
      ),
      lessons: this.lessonsControl = new FormControl(
        this.group.getLessonsId()
      )
    });
  }
  cancel(): void {
    this.router.navigate(['/admin/groups']);
  }
  saveGroup(): void {
    this.invalidForm = !this.groupForm.valid;
    if (this.groupForm.valid) {
      this.loaderService.display(true);
      // New ?
      const newGroup = this.group.id ? false : true;
      // SAVE
      this.applyFormData();
      (this.group.id ? this.restService.updateDb('group', this._group) : this.restService.addDb('groups', this._group))
        .then(response => {
          if (response.success) {
            this.groupForm.markAsPristine();
            this.dirtyChange.emit(this.groupForm.dirty);
            this.getEntity(response.group);
            this.alertService.configWaitingAlert('Le groupe a bien été ' + (newGroup ? 'créé.' : 'modifié.'));
          } else {
            this.checksBack(false, response.errors);
            this.alertService.configWaitingAlert('Impossible d\'enregistrer. Vérifier les champs en rouge.', 'error');
          }
          this.loaderService.display(false);
          return response;
        })
        .then(response => {
          if (response.success) {
            this.checksBack();
          }
          // CHECKS
          this.groupForm.markAsPristine();
          this.groupForm.markAsUntouched();
          this.checksFront();
          if (response.success) {
            this.router.navigate(['/admin/groups']);
          }
        })
        .catch(() => {
          console.error(`Group \'${this.group.name}\' cannot be changed in the database.`);
          this.alertService.configWaitingAlert('Une erreur est survenue...', 'error');
          this.loaderService.display(false);
        });
    }
  }

  //
  getEntity(entity) {
    this.group = new Group(entity);
  }
  applyFormData(): void {
    this._group = Object.assign({}, this.group);
    this._group.name = this.nameControl.value;
    this._group.users = this.studentsControl.value;
    this._group.lessons = this.lessonsControl.value;
  }

}

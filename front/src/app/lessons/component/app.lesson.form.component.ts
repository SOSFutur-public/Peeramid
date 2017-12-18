import { Component, Input, OnInit, EventEmitter, Output, OnDestroy, AfterViewInit, AfterViewChecked, ElementRef, ViewChild } from '@angular/core';
import { FormBuilder, FormControl, FormGroup } from '@angular/forms';
import { Router } from '@angular/router';
import { FileUploader } from 'ng2-file-upload';
import { IMultiSelectSettings, IMultiSelectTexts } from 'angular-2-dropdown-multiselect';
import { isUndefined } from 'ngx-bootstrap/bs-moment/utils/type-checks';

// Environment
import { environment } from '../../../environments/environment';

// Classes
import { Lesson } from '../class/app.lesson.class';

// Functions
import { joinKeyFromAssociativeArrays } from '../../core/functions/app.core.utils.functions';

// Services
import { AppCoreRestService } from '../../core/service/app.core.rest.service';
import { AppCoreFormService } from '../../core/service/app.core.form.service';
import { AppAuthAuthenticationService } from '../../auth/service/app.auth.authentication.service';
import { AppCoreAlertService } from '../../core/service/app.core.alert.service';
import { FileType } from '../../core/class/app.file.class';
import { AppCoreLoaderService } from '../../core/service/app.core.loader.service';

// -----

@Component ({
  selector: 'app-lesson-form',
  templateUrl: '../html/app.lesson.form.component.html',
  styleUrls: ['../../../assets/css/app.core.form.component.scss']
})
export class AppLessonFormComponent implements OnInit, OnDestroy, AfterViewInit, AfterViewChecked {

  @Input() lesson: Lesson;
  @Input() studentsEditOnly: boolean;
  @Input() dirty: boolean;
  @Output() dirtyChange = new EventEmitter<boolean>();
  _lesson: any;
  // ENV
  environment = environment;
  // FORM
  lessonForm: FormGroup;
  invalidForm: boolean;
  // CONTROL
  nameControl: FormControl;
  categoriesControl: FormControl;
  descriptionControl: FormControl;
  studentsControl: FormControl;
  teachersControl: FormControl;
  imageControl: FormControl;
  // IMAGE
  @ViewChild('uploaderElem') uploaderElemRef: ElementRef;
  uploader: FileUploader;
  filesMaxSizeSetting: number;
  filesTypesSetting: FileType[] = [];
  filesTypesSettingText: string;
  // SELECT
  categories: {}[] = [];
  students: {}[] = [];
  teachers: {}[] = [];
  singleSelectSettings: IMultiSelectSettings;
  multipleSelectSettings: IMultiSelectSettings;
  categoriesSelectTexts: IMultiSelectTexts;
  studentsSelectTexts: IMultiSelectTexts;
  teachersSelectTexts: IMultiSelectTexts;
  // WYSIWYG
  editors = {};
  // ERRORS
  backChecks = null;
  saveChecks = null;
  backControls = {};
  saveControls = {};


  constructor(
    private formBuilder: FormBuilder,
    private router: Router,
    private restService: AppCoreRestService,
    private formService: AppCoreFormService,
    private authService: AppAuthAuthenticationService,
    private loaderService: AppCoreLoaderService,
    private alertService: AppCoreAlertService,
  ) {
    console.log('__CONSTRUCT__ app.lesson.form.component');
    this.authService.checkRole(['admin', 'teacher'], true);
    setInterval(() => {
      this.dirtyChange.emit(this.lessonForm.dirty);
    }, 1000);
  }

  ngOnInit(): void {
    this.studentsEditOnly = (!(isUndefined(this.studentsEditOnly) || !this.studentsEditOnly));
    this.invalidForm = false;
    // SELECT
    this.multipleSelectSettings = this.formService.multipleSelectSettings();
    this.singleSelectSettings = this.formService.singleSelectSettings();
    this.getTeachers();
    this.getStudents();
    if (!this.studentsEditOnly) {
      this.getCategories();
      // FILES
      this.getFilesSettings();
      this.uploader = new FileUploader({
        headers: [{name: 'Authorization' , value: 'Bearer ' + this.authService.getToken()}],
        url: environment.api_url + 'lessons/' + this.lesson.id + '/images',
        removeAfterUpload: true
      });
      this.uploader.onAfterAddingFile = () => {
        this.uploaderElemRef.nativeElement.value = '';
        this.checksFront();
        this.imageControl.markAsDirty();
      };
      this.uploader.onCompleteItem = (item: any, response: any, status: any, headers: any) => {
        response = JSON.parse(response);
        response.success ? this.getEntity(response.lesson) : null;
        this.loaderService.display(false);
      };
    }
    // FORM
    this.setForm();
    // CHECKS
    this.checks();
    this.checksFront(true);
    this.checksBack(true);
  }

  // WYSIWYG
  ngAfterViewInit() {
    if (!this.studentsEditOnly && this.lesson && this.lessonForm && Object.keys(this.editors).length === 0) {
      this.formService.wysiwyg(this.editors, 'description', this.descriptionControl);
    }
  }
  ngAfterViewChecked() {
    this.ngAfterViewInit();
  }
  ngOnDestroy() {
    this.formService.wysiwyg_remove(this.editors);
  }

  // CHECKS
  checks() {
    this.lessonForm.valueChanges.subscribe(data => { this.checksFront(); });
  }
  checksFront(init: boolean = false) {
    if (init) {
      this.saveControls = {
        'studentEditOnly':   [this.studentsEditOnly,  {}, 'studentEditOnly', null],
        'name':     [this.nameControl,    {
          'required' : 'L\'intitulé du cours est requis.'
        }, 'Intitulé du cours', 'studentEditOnly:false'],
        'category':   [this.categoriesControl,  {
          'required': 'Veuillez sélectionner une catégorie.',
          'maxNumber:1': 'Vous ne pouvez sélectionner qu\'une seule catégorie.'
        }, 'Catégorie du cours', 'studentEditOnly:false'],
        'description':   [this.descriptionControl,  {}, 'Description', null],
        'students':   [this.studentsControl,  {}, 'Liste des étudiants', null],
        'teachers':   [this.teachersControl,  {}, 'Liste des professeurs', null],
        'imageFile': [['file', this.uploader, this.filesMaxSizeSetting, this.filesTypesSetting], {
          'fileType': 'Le type de fichier n\'est pas accepté.',
          'fileSize': 'Le fichier est trop volumineux.'
        }, 'Image du cours', 'studentEditOnly:false'],
      };
    }
    this.saveChecks = this.formService.checkFrontErrors(this.saveControls);
  }
  checksBack(init: boolean = false, response = []) {
    if (init) {
      this.backControls = [
        'name',
        'category',
        'description',
        'studentEditOnly',
        'students',
        'teachers',
        'imageFile'
      ];
    }
    this.backChecks = this.formService.checkBackErrors(this.backControls, response);
  }

  // SELECT
  getCategories(): void {
    this.loaderService.display(true);
    this.categoriesSelectTexts = this.formService.selectTexts(' la catégorie...');
    this.restService.getDb('categories')
      .then(categories => {
        for (const categorie of categories) {
          this.categories.push({ id: categorie.id, name: categorie.name });
        }
        this.loaderService.display(false);
      });
  }
  getStudents(): void {
    this.loaderService.display(true);
    this.studentsSelectTexts = this.formService.selectTexts(' des étudiants...');
    this.restService.getDb('students')
      .then(students => {
        for (const student of students) {
          this.students.push({ id: student.id, name: student.last_name + ' ' + student.first_name });
        }
        this.loaderService.display(false);
      });
  }
  getTeachers(): void {
    this.loaderService.display(true);
    this.teachersSelectTexts = this.formService.selectTexts(' des professeurs...');
    this.restService.getDb('teachers')
      .then(teachers => {
        for (const teacher of teachers) {
          this.teachers.push({ id: teacher.id, name: teacher.last_name + ' ' + teacher.first_name });
        }
        this.loaderService.display(false);
      });
  }
  getFilesSettings(): void {
    this.loaderService.display(true);
    this.restService.getDb('maxSizeSetting')
      .then(maxSizeSetting => this.filesMaxSizeSetting = parseInt(maxSizeSetting.value, 10))
      .then(() => {
        this.loaderService.display(true);
        this.restService.getDb('imagesFileTypes')
          .then(imageFileTypes => imageFileTypes.forEach(imageFileType => this.filesTypesSetting.push(new FileType(imageFileType))))
          .then(() => {
            this.filesTypesSettingText = joinKeyFromAssociativeArrays(this.filesTypesSetting, 'type');
            this.checksFront(true);
            this.loaderService.display(false);
          });
        this.loaderService.display(false);
      });
  }

  // FILES
  removeFile(): void {
    console.log('pas queue');
    this.loaderService.display(true);
    this.restService.deleteDb('lesson_image', [this.lesson.id])
      .then(response => {
        if (response.success) {
          response.success ? this.getEntity(response.lesson) : null;
        }
        this.loaderService.display(false);
      });
  }
  removeQueuedFile(item: any): void {
    console.log('queue');
    item.remove();
    this.checksFront();
  }

  // FORM
  setForm(): void {
    this.lessonForm = this.formBuilder.group({
      name: this.nameControl = new FormControl(
        this.lesson.name,
        //Validators.required
      ),
      category: this.categoriesControl = new FormControl(
        this.lesson.getCategoryId(),
        //Validators.required
      ),
      description: this.descriptionControl = new FormControl(
        this.lesson.description
      ),
      students: this.studentsControl = new FormControl(
        this.lesson.getStudentsId()
      ),
      teachers: this.teachersControl = new FormControl(
        this.lesson.getTeachersId()
      ),
      image: this.imageControl = new FormControl(
        this.lesson.image
      )
    });
  }
  cancel(): void {
    this.router.navigate(this.studentsEditOnly ? ['/teacher/lesson', this.lesson.id] : ['/admin/lessons']);
  }
  saveLesson(): void {
    if (this.formService.checkEmptyChecks(this.saveChecks)) {
      this.loaderService.display(true);
      // New ?
      const newLesson = this.lesson.id ? false : true;
      // SAVE
      this.applyFormData();
      (this.lesson.id ? this.restService.updateDb('lesson', this._lesson) : this.restService.addDb('lessons', this._lesson))
        .then(response => {
          if (response.success) {
            // EVAL
            this.getEntity(response.lesson);
            // FILES
            if (!this.studentsEditOnly) {
              if (this.uploader.options.url === environment.api_url + 'lessons/null/images') {
                this.formService.upload([this.uploader], environment.api_url + 'lessons/' + this.lesson.id + '/images');
              } else {
                this.formService.upload([this.uploader]);
              }
            }
          } else {
            this.checksBack(false, response.errors);
            this.alertService.configWaitingAlert('Impossible d\'enregistrer. Vérifier les champs en rouge.', 'error');
          }
          return response;
        })
        .then(response => {
          if (response.success) {
            this.checksBack();
            // ALERT
            this.alertService.configWaitingAlert('Le Cours a bien été ' + (newLesson ? 'créé.' : 'modifié.'));
          }
          // CHECKS
          this.lessonForm.markAsPristine();
          this.lessonForm.markAsUntouched();
          this.checksFront();
          // REDIRECT
          if (response.success) {
            this.dirtyChange.emit(this.lessonForm.dirty);
            this.studentsEditOnly ? this.router.navigate(['/teacher/lesson/' + this.lesson.id]) : this.router.navigate(['/admin/lessons']);
          }
          // LOADER
          this.loaderService.display(false);
        })
        .catch(() => {
          console.error(`Evaluation \'${this.lesson.name}\' cannot be changed in the database.`);
          this.alertService.configWaitingAlert('Une erreur est survenue...', 'error');
          this.loaderService.display(false);
        });
    }
  }

  //
  getEntity(entity) {
    this.lesson = new Lesson(entity);
  }
  applyFormData(): void {
    this._lesson = Object.assign({}, this.lesson);
    if (!this.studentsEditOnly) {
      this._lesson.name = this.lessonForm.value.name;
      this._lesson.category = this.categoriesControl.value[0];
      this._lesson.description = this.descriptionControl.value;
    }
    this._lesson.users = this.studentsControl.value.concat(this.teachersControl.value);
  }
}

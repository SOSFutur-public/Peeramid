import { Component, EventEmitter, Input, OnInit, Output } from '@angular/core';
import { FormBuilder, FormControl, FormGroup } from '@angular/forms';
import { FileUploader } from 'ng2-file-upload';

// Environment
import { environment} from '../../../../environments/environment';

// Classes
import { User } from '../../class/app.user.class';
import { FileType } from '../../../core/class/app.file.class';

// Animations
import { slideInOutAnimation } from '../../../../animations/slide.animations';

// Functions
import { joinKeyFromAssociativeArrays } from '../../../core/functions/app.core.utils.functions';

// Services
import { AppCoreRestService } from '../../../core/service/app.core.rest.service';
import { AppAuthAuthenticationService } from '../../../auth/service/app.auth.authentication.service';
import { AppCoreFormService } from '../../../core/service/app.core.form.service';
import { AppCoreAlertService } from '../../../core/service/app.core.alert.service';
import { AppCoreLoaderService } from '../../../core/service/app.core.loader.service';

// -----

@Component ({
  selector: 'app-students-import',
  templateUrl: '../html/app.student.import.component.html',
  styleUrls: ['../../../../assets/css/app.students.import.component.scss'],
  animations: [slideInOutAnimation],
  host: { '[@slideInOutAnimation]': '' }
})
export class AppStudentImportComponent implements OnInit {

  @Input() getViewImport: Boolean;
  @Input() students: User[];
  @Output() getViewImportChange = new EventEmitter();
  @Output() studentsChange = new EventEmitter();
  // FORM
  importUsersForm: FormGroup;
  invalidForm: boolean;
  // CONTROLS
  importControl: FormControl;
  // IMAGE
  uploader: FileUploader;
  uploadErrors: string[][] = [];
  filesMaxSizeSetting: number;
  filesTypesSetting: FileType[] = [];
  filesTypesSettingText: string;
  // ERRORS
  saveChecks;
  backChecks;
  saveControls = {};
  backControls = {};
  created = 0;
  nocreated = 0;
  errors = [];

  constructor(
    private formBuilder: FormBuilder,
    private restService: AppCoreRestService,
    private authService: AppAuthAuthenticationService,
    private loaderService: AppCoreLoaderService,
    private alertService: AppCoreAlertService,
    private formService: AppCoreFormService,
  ) {
    console.log('__CONSTRUCT__ app.student.import.component');
    this.authService.checkRole(['admin'], true);
  }

  ngOnInit(): void {
    this.invalidForm = false;
    // FILES
    this.getFilesSettings();
    this.uploader = new FileUploader({
      headers: [{name: 'Authorization' , value: 'Bearer ' + this.authService.getToken()}],
      url: environment.api_url + 'users/list',
      removeAfterUpload: true
    });
    this.uploader.onAfterAddingFile = () => {
      this.checksFront();
      this.importControl.markAsDirty();
    };
    this.uploader.onCompleteItem = (item: any, response: any, status: any, headers: any) => {
      response = JSON.parse(response);
      if (status === 200 && response.success) {
        this.created = response.created_users.length;
        this.nocreated = response.error_users.length;
        response.created_users.forEach(user => this.students.push(new User(user)));
        if (response.created_users.length > 0) {
          this.alertService.setAlert(response.created_users.length + ' Étudiant' + (response.created_users.length > 1 ? 's' : '' ) + (response.created_users.length > 1 ? ' ont ' : ' a ' ) + ' été ajouté' + (response.created_users.length > 1 ? 's' : '' ) + ' !', 'success');
        }
        for (const error of response.error_users) {
          const errors = [];
          for (const e of error.errors) {
            errors.push([['Champ : ' + e.field], ['Valeur : ' + e.value], ['Erreur : ' + e.message]]);
          }
          this.errors.push([error.user.last_name + ' ' + error.user.first_name, errors]);
        }
        if (this.errors.length === 0) {
          this.cancel();
        }
      } else {
        this.alertService.setAlert('Un problème est survenu lors de l\'import. Veuillez réessayer.', 'error');
        this.cancel();
      }
      this.loaderService.display(false);
    };
    // FORM
    this.setForm();
    // CHECKS
    this.checks();
    this.checksFront(true);
    this.checksBack(true);
  }

  // CHECKS
  checks() {
    this.importUsersForm.valueChanges.subscribe(data => { this.checksFront(); });
  }
  checksFront(init: boolean = false) {
    if (init) {
      this.saveControls = {
        'file': [['file', this.uploader, this.filesMaxSizeSetting, this.filesTypesSetting], {
          'required' : 'Un fichier est requis.',
          'fileType': 'Le type de fichier n\'est pas accepté.',
          'fileSize': 'Le fichier est trop volumineux.'
        }, 'Fichier', null],
      };
    }
    this.saveChecks = this.formService.checkFrontErrors(this.saveControls);
  }
  checksBack(init: boolean = false, response = []) {
    if (init) {
      this.backControls = [
        'file',
      ];
    }
    this.backChecks = this.formService.checkBackErrors(this.backControls, response);
  }

  // FORM
  setForm(): void {
    this.importUsersForm = this.formBuilder.group({
      image: this.importControl = new FormControl()
    });
  }

  // IMAGE
  getFilesSettings(): void {
    this.loaderService.display(true);
    this.restService.getDb('maxSizeSetting')
      .then(maxSizeSetting => this.filesMaxSizeSetting = parseInt(maxSizeSetting.value, 10))
      .then(() => {
        this.restService.getDb('csvFileTypes')
          .then(imageFileTypes => imageFileTypes.forEach(imageFileType => this.filesTypesSetting.push(new FileType(imageFileType))))
          .then(() => {
            this.filesTypesSettingText = joinKeyFromAssociativeArrays(this.filesTypesSetting, 'type');
            this.checksFront(true);
          });
        this.loaderService.display(false);
      });
  }

  removeQueuedFile(item: any): void {
    item.remove();
    this.checksFront();
  }
  cancel(): void {
    this.getViewImport = false;
    this.getViewImportChange.emit(false);
    this.studentsChange.emit(this.students);
  }

  importUsers(): void {
    this.errors = [];
    this.created = 0;
    this.nocreated = 0;
    this.invalidForm = !this.importUsersForm.valid;
    if (this.importUsersForm.valid === true) {
      if (this.uploader.queue.length > 0) {
        this.loaderService.display(true);
        this.uploader.queue[0].upload();
      }
    }
  }

}

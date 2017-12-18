import { Component, Input, Output, OnInit, EventEmitter, ViewChild, ElementRef } from '@angular/core';
import { FormBuilder, FormControl, FormGroup, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { FileUploader } from 'ng2-file-upload';
import { IMultiSelectSettings, IMultiSelectTexts } from 'angular-2-dropdown-multiselect';

// Environment
import { environment } from '../../../../environments/environment';

// Classes
import { User } from '../../class/app.user.class';
import { FileType } from '../../../core/class/app.file.class';

// Services
import { AppCoreRestService } from '../../../core/service/app.core.rest.service';
import { AppCoreFormService } from '../../../core/service/app.core.form.service';
import { AppAuthAuthenticationService } from '../../../auth/service/app.auth.authentication.service';
import { AppCoreAlertService } from '../../../core/service/app.core.alert.service';
import { AppCoreLoaderService } from '../../../core/service/app.core.loader.service';

// Validators
import { MatchingPasswordsValidator, StrongPasswordValidator } from '../../../core/validator/app.core.matching.passwords.validator';

// -----

@Component ({
  selector: 'app-user-form',
  templateUrl: '../html/app.user.form.component.html',
  styleUrls: ['../../../../assets/css/app.core.form.component.scss']
})
export class AppUserFormComponent implements OnInit {

  @Input() user: User;
  @Input() dirty: boolean;
  @Output() dirtyChange = new EventEmitter<boolean>();
  _user: any;
  // ENV
  environment = environment;
  // FORM
  userForm: FormGroup;
  invalidForm: boolean;
  // CONTROLS
  lastNameControl: FormControl;
  firstNameControl: FormControl;
  emailControl: FormControl;
  usernameControl: FormControl;
  passwordControl: FormControl;
  passwordConfirmControl: FormControl;
  lessonsControl: FormControl;
  groupsControl: FormControl;
  imageControl: FormControl;
  // IMAGE
  @ViewChild('uploaderElem') uploaderElemRef: ElementRef;
  uploader: FileUploader;
  filesMaxSizeSetting: number;
  filesTypesSetting: FileType[];
  // SELECT
  lessons: {}[] = [];
  groups: {}[] = [];
  multipleSelectSettings: IMultiSelectSettings;
  lessonsSelectTexts: IMultiSelectTexts;
  groupsSelectTexts: IMultiSelectTexts;
  // ERRORS
  backChecks = null;
  saveChecks = null;
  backControls = {};
  saveControls = {};
  // OTHERS
  itself = false;

  constructor(
    private formBuilder: FormBuilder,
    private router: Router,
    private restService: AppCoreRestService,
    private formService: AppCoreFormService,
    private authService: AppAuthAuthenticationService,
    private loaderService: AppCoreLoaderService,
    private alertService: AppCoreAlertService,
  ) {
    console.log('__CONSTRUCT__ app.user.form.component');
    this.authService.checkRole(['admin', 'student', 'teacher'], true);
    setInterval(() => {
      this.dirtyChange.emit(this.userForm.dirty);
    }, 1000);
  }

  ngOnInit(): void {
    this.itself = this.user.id === this.authService.user.id;
    this.invalidForm = false;
    // SELECT
    this.multipleSelectSettings = this.formService.multipleSelectSettings();
    if (!this.itself) {
      this.getLessons();
      this.getGroups();
    }
    // FILES
    this.getMaxSizeSetting();
    this.getImageFileTypes();
    this.uploader = new FileUploader({
      headers: [{name: 'Authorization' , value: 'Bearer ' + this.authService.getToken()}],
      url: environment.api_url + 'users/' + this.user.id + '/images',
      removeAfterUpload: true
    });
    this.uploader.onAfterAddingFile = () => {
      this.uploaderElemRef.nativeElement.value = '';
      this.checksFront();
    };
    this.uploader.onCompleteItem = (item: any, response: any, status: any, headers: any) => {
      response = JSON.parse(response);
      if (response.success) {
        this.getEntity(response.user);
      } else {
        this.alertService.configWaitingAlert('L\'image n\'a pas pu être importée.', 'error');
      }
      this.loaderService.display(false);
    };
    // FORM
    this.setForm();
    // CHECKS
    this.checks();
    this.checksBack(true);
  }
  //CHECKS
  checks() {
    this.userForm.valueChanges.subscribe(data => { this.checksFront(); });
  }
  checksFront(init: boolean = false) {
    // Function
    if (init) {
      this.saveControls = {
        'id':     [this.user.id,    {}, 'Id', null],
        'role':   [this.user.role.id,    {}, 'Role', null],
        'firstName':     [this.firstNameControl,    {
          'required' : 'Le prénom est requis.',
          'regex:^[a-zA-ZàâäÀÂÄéèêëÉÈÊËîïÎÏùûüÛÙÜôöÔÖçÇ\\\-\\\' ]+$' : 'Le prénom est invalide.',
        }, 'Prénom', null],
        'lastName':     [this.lastNameControl,    {
          'required' : 'Le nom est requis.',
          'regex:^[a-zA-ZàâäÀÂÄéèêëÉÈÊËîïÎÏùûüÛÙÜôöÔÖçÇ\\\-\\\' ]+$' : 'Le nom est invalide.'
        }, 'Nom', null],
        'email':     [this.emailControl,    {
          'required' : 'L\'email est requis.',
          'regex:^[a-z0-9._-]+@[a-z0-9._-]{2,}\\\.[a-z]{2,4}$' : 'L\'email est invalide.'
        }, 'Email', null],
        'username':     [this.usernameControl,    {
          'required' : 'L\'identifiant est requis.',
          'regex:^[0-9a-zA-ZàâäÀÂÄéèêëÉÈÊËîïÎÏùûüÛÙÜôöÔÖçÇ._\\\- ]+$' : 'L\'identifiant est invalide.'
        }, 'Identifiant', null],
        'password':     [this.passwordControl,    {
          'regex:^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[,;.+=_?!@#$%^&*-])(?=.{8,})' : 'Le mot de passe doit contenir au moins 8 caractères et avec au moins 1 minuscule, 1 majuscule, 1 chiffre et 1 caractère spécial.'
        }, 'Mot de passe', 'id:!null'],
        'passwordConfirm':     [this.passwordConfirmControl,    {
          'required' : 'La confirmation du mot de passe est requise.',
          'same:password' : 'La confirmation du mot de passe doit être identique au mot de passe.'
        }, 'Confirmation du mot de passe', 'id:!null&&password:!'],
        'lessons':     [this.lessonsControl,    {}, 'Liste des cours', null],
        'groups':     [this.groupsControl,    {}, 'Liste des groupes', 'role:2'],
        'imageFile':     [['file', this.uploader, this.filesMaxSizeSetting, this.filesTypesSetting], {
          'fileType': 'Le type de fichier n\'est pas accepté.',
          'fileSize': 'Le fichier est trop volumineux.'
        }, 'Image', 'id:!null'],
      };
    }
    this.saveChecks = this.formService.checkFrontErrors(this.saveControls);
  }
  checksBack(init: boolean = false, response = []) {
    if (init) {
      this.backControls = [
        'firstName',
        'lastName',
        'email',
        'username',
        'password',
        'passwordConfirm',
        'lessons',
        'groups',
        'image'
      ];
    }
    this.backChecks = this.formService.checkBackErrors(this.backControls, response);
  }

  // SELECT
  getLessons(): void {
    this.loaderService.display(true);
    this.lessonsSelectTexts = this.formService.selectTexts(' les cours...');
    this.restService.getDb('lessons')
      .then(lessons => {
        for (const lesson of lessons) {
          this.lessons.push({ id: lesson.id, name: lesson.name });
        }
        this.loaderService.display(false);
      });
  }
  getGroups(): void {
    this.loaderService.display(true);
    this.groupsSelectTexts = this.formService.selectTexts(' les groupes...');
    this.restService.getDb('groups')
      .then(groups => {
        for (const group of groups) {
          this.groups.push({ id: group.id, name: group.name });
        }
        this.loaderService.display(false);
      });
  }

  getMaxSizeSetting(): void {
    this.loaderService.display(true);
    this.restService.getDb('maxSizeSetting')
      .then(maxSizeSetting => {
        this.filesMaxSizeSetting = parseInt(maxSizeSetting.value, 10);
        this.checksFront(true);
        this.loaderService.display(false);
      });
  }
  getImageFileTypes(): void {
    this.loaderService.display(true);
    this.filesTypesSetting = [];
    this.restService.getDb('imagesFileTypes')
      .then(imageFileTypes => {
        imageFileTypes.forEach(imageFileType => this.filesTypesSetting.push(new FileType(imageFileType)));
        this.checksFront(true);
        this.loaderService.display(false);
      });
  }

  // FILES
  removeFile() {
    this.loaderService.display(true);
    this.restService.deleteDb('userImage', [this.user.id])
      .then(response => {
        if (response.success) {
          this.getEntity(response.user);
        }
        this.loaderService.display(false);
      });
  }
  removeQueuedFile(item: any) {
    item.remove();
    this.checksFront();
  }

  // FORM
  setForm(): void {
    this.userForm = this.formBuilder.group({
      lastName: this.lastNameControl = new FormControl(
        this.user.last_name,
        /*Validators.compose([
          Validators.required,
          Validators.pattern('^[a-zA-ZàâäÀÂÄéèêëÉÈÊËîïÎÏùûüÛÙÜôöÔÖçÇ\\-\\\' ]+$')
        ])*/
      ),
      firstName: this.firstNameControl = new FormControl(
        this.user.first_name,
        /*Validators.compose([
          Validators.required,
          Validators.pattern('^[a-zA-ZàâäÀÂÄéèêëÉÈÊËîïÎÏùûüÛÙÜôöÔÖçÇ\\-\\\' ]+$')
        ])*/
      ),
      email: this.emailControl = new FormControl(
        this.user.email,
        /*Validators.compose([
          Validators.required,
          Validators.pattern('^[a-z0-9._-]+@[a-z0-9._-]{2,}\\.[a-z]{2,4}$')
        ])*/
      ),
      username: this.usernameControl = new FormControl(
        this.user.username,
        /*Validators.compose([
          Validators.required,
          Validators.pattern('^[0-9a-zA-ZàâäÀÂÄéèêëÉÈÊËîïÎÏùûüÛÙÜôöÔÖçÇ,.;:()_\x22\\-\\\' ]+$')
        ])*/
      ),
      passwordGroup: this.formBuilder.group({
          password: this.passwordControl = new FormControl(
            '',
            //Validators.compose([StrongPasswordValidator()])
          ),
          passwordConfirm: this.passwordConfirmControl = new FormControl(
            '',
            //Validators.compose([])
          )
        },
        {
          //validator: MatchingPasswordsValidator()
        }),
      lessons: this.lessonsControl = new FormControl(
        this.user.getLessonsId()
      ),
      groups: this.groupsControl = new FormControl(
        this.user.getGroupsId()
      ),
      image: this.imageControl = new FormControl(
        this.user.image
      )
    });
  }
  cancel(): void {
    let userType: string;

    userType = (this.user.role.id === 2 ? 'students' : 'teachers');
    this.itself ? this.router.navigate([this.authService.user.role.getUrl(), 'home']) : this.router.navigate(['/admin', userType]);
  }
  saveUser(): void {
    this.invalidForm = !this.userForm.valid;
    if (this.formService.checkEmptyChecks(this.saveChecks)) {
      this.loaderService.display(true);
      // New ?
      const newUser = this.user.id ? false : true;
      // Type
      const userType = (this.user.role.id === 2 ? 'students' : 'teachers');
      // SAVE
      this.applyFormData();
      (this.itself ? (this.restService.updateDb('userProfile', this._user)) : (this.user.id ? this.restService.updateDb('user', this._user) : this.restService.addDb('users', this._user)))
        .then(response => {
          if (response.success) {
            this.userForm.markAsPristine();
            this.dirtyChange.emit(this.userForm.dirty);
            // EVAL
            this.getEntity(response.user);
            // FILES
            if (this.uploader.options.url === environment.api_url + 'users/null/images') {
              this.formService.upload([this.uploader], environment.api_url + 'users/' + this.user.id + '/images');
            } else {
              this.formService.upload([this.uploader]);
            }
          } else {
            this.checksBack(false, response.errors);
            this.alertService.configWaitingAlert('Impossible d\'enregistrer. Vérifier les champs en rouge.', 'error');
          }
          return response;
        })
        .then(response => {
          if (response.success) {
            if (this.itself) {
              this.alertService.configWaitingAlert('Votre profil a bien été modifié !', 'success');
            } else {
              this.alertService.configWaitingAlert('L\'' + (this.user.role.id === 2 ? 'étudiant' : 'enseignant') + ' a bien été ' + (newUser ? 'créé.' : 'modifié.'), 'success');
            }
            this.checksBack();
          }
          // CHECKS
          this.userForm.markAsPristine();
          this.userForm.markAsUntouched();
          this.checksFront();
          if (response.success && !this.itself) {
            this.router.navigate(['/admin', userType]);
          }
          // LOADER
          this.loaderService.display(false);
        })
        .catch(() => {
          console.error(`User \'${this.user.username}\' cannot be changed in the database.`);
          this.alertService.configWaitingAlert('Une erreur est survenue...', 'error');
          this.loaderService.display(false);
        });
    }
  }

  //
  getEntity(entity) {
    this.user = new User(entity);
    if (this.itself) {
      this.authService.getLoggedUser();
    }
  }
  applyFormData(): void {
    this._user = Object.assign({}, this.user);
    this._user.last_name = this.lastNameControl.value;
    this._user.first_name = this.firstNameControl.value;
    this._user.email = this.emailControl.value;
    this._user.username = this.usernameControl.value;
    this._user.password = (this.passwordControl.pristine ? null : this.passwordControl.value);
    this._user.role = this.user.role.id;
    this._user.groups = this.groupsControl.value;
    this._user.lessons = this.lessonsControl.value;
  }

}

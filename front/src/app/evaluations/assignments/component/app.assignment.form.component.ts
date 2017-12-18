import { AfterViewChecked, AfterViewInit, Component, OnDestroy, OnInit, ViewChild, ElementRef } from '@angular/core';
import { FormGroup, FormArray, FormControl } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { FileUploader } from 'ng2-file-upload';
import { isNullOrUndefined } from 'util';

// Environment
import { environment } from '../../../../environments/environment';

// Classes
import { Assignment } from '../../class/app.evaluation.class';
import { FileType } from '../../../core/class/app.file.class';

// Animations
import { routerAnimation } from '../../../../animations/router.animations';

// Services
import { AppCoreRestService } from '../../../core/service/app.core.rest.service';
import { AppAuthAuthenticationService } from '../../../auth/service/app.auth.authentication.service';
import { AppCoreFormService } from '../../../core/service/app.core.form.service';
import { AppCoreAlertService } from '../../../core/service/app.core.alert.service';
import { AppCoreLoaderService } from '../../../core/service/app.core.loader.service';
import { AppCoreFilterService } from '../../../core/service/app.core.filter.service';

// -----

@Component ({
  selector: 'app-assignment-instruction',
  templateUrl: '../html/app.assignment.form.component.html',
  animations: [routerAnimation],
  host: { '[@routerAnimation]': '' }
})
export class AppAssignmentFormComponent implements OnInit, OnDestroy, AfterViewInit, AfterViewChecked {

  // ENV
  environment = environment;
  assignment: Assignment;
  _assignment: any;
  finished: boolean;
  // FORM
  assignmentForm: FormGroup;
  // CONTROLS
  sectionControls: FormControl[];
  // VIEWS
  view_instructions: Boolean = false;
  // FILES
  @ViewChild('uploadersElem') uploadersElemRef: ElementRef;
  uploaders: FileUploader[] = [];
  filesSize: number[] = [];
  filesTypes: FileType[][] = [];
  uploadErrors: string[][] = [];
  // WYSIWYG
  editors = {};

  constructor(
    private route: ActivatedRoute,
    private router: Router,
    private restService: AppCoreRestService,
    private authService: AppAuthAuthenticationService,
    private loaderService: AppCoreLoaderService,
    private alertService: AppCoreAlertService,
    private formService: AppCoreFormService,
    private filterService: AppCoreFilterService
  ) {
    console.log('__CONSTRUCT__ app.assignment.form.component');
    this.authService.checkRole(['student', 'teacher'], true);
  }

  ngOnInit(): void {
    // GET
    this.getAssignment();
  }

  canDeactivate() {
    if (this.assignmentForm && this.assignmentForm.dirty) {
      return confirm('Êtes-vous sûr de quitter le formulaire sans enregistrer vos modifications?');
    }
    return true;
  }

  // WYSIWYG
  ngAfterViewInit() {
    if (this.assignment && this.assignmentForm && Object.keys(this.editors).length === 0) {
      this.assignment.assignment_sections.forEach(assignmentSection => {
        if (assignmentSection.section.section_type.id === 1) {
          this.formService.wysiwyg(this.editors, assignmentSection.section.order - 1, this.sectionControls[assignmentSection.section.order - 1], this.finished || this.authService.user.role.id === 3);
        }
      });
    }
  }
  ngAfterViewChecked() {
    this.ngAfterViewInit();
  }
  ngOnDestroy() {
    this.editors = this.formService.wysiwyg_remove(this.editors);
  }

  // GET
  getAssignment(): void {
    this.loaderService.display(true);
    this.restService.getDb('assignments', [+this.route.snapshot.params['id']])
      .then(assignment => this.getEntity(assignment))
      .then(() => {
        this.finished = this.assignment.isFinished();
        this.filterService.sortList(this.assignment.assignment_sections, 'section.order', true);
        this.setForm();
      })
      .then(() => {
        this.assignment.assignment_sections.forEach(assignmentSection => {
          // FILES
          if (assignmentSection.section.section_type.id === 2) {
            this.uploaders[assignmentSection.id] = new FileUploader({
              headers: [{name: 'Authorization' , value: 'Bearer ' + this.authService.getToken()}],
              url: environment.api_url + 'assignmentsections/' + assignmentSection.id + '/files',
              removeAfterUpload: true
            });
            this.uploaders[assignmentSection.id].onAfterAddingFile = () => {
              this.uploadersElemRef.nativeElement.value = '';
            };
            this.uploaders[assignmentSection.id].onCompleteItem = (item: any, response: any, status: any, headers: any) => {
              response = JSON.parse(response);
              if (response.success) {
                this.getEntity(response.assignment);
              } else {
                this.alertService.configWaitingAlert('Le fichier de la section ' + assignmentSection.section.title + ' n\'a pas pu être importé.', 'error');
              }
              this.loaderService.display(false);
            };
            this.filesSize[assignmentSection.id] = assignmentSection.section.max_size;
            this.filesTypes[assignmentSection.id] = assignmentSection.section.file_types;
          }
        });
        this.loaderService.display(false);
      });
  }

  // FILES
  removeFile(id: number): void {
    this.loaderService.display(true);
    this.restService.deleteDb('assignmentSection_files', [id])
      .then(response => {
        response.success ? this.getEntity(response.assignment) : null;
      })
      .then(() => {
        this.loaderService.display(false);
      });
  }
  removeQueuedItem(id: number, item: any): void {
    item.remove();
    this.uploadErrors[id] = null;
  }

  setForm(): void {
    this.assignmentForm = new FormGroup({
      sections: new FormArray(this.createSectionControls())
    });
  }
  createSectionControls(): FormControl[] {
    this.sectionControls = [];
    this.assignment.assignment_sections.forEach(assignmentSection => {
      this.sectionControls[assignmentSection.section.order - 1] = new FormControl({
        value: assignmentSection.answer,
        disabled: this.finished || this.authService.user.role.id === 3
      });
    });
    return this.sectionControls;
  }

  alertEmptySections(): boolean {
    let message: string;

    message = '';
    this.assignment.assignment_sections.forEach((assignmentSection, index) => {
      if ((assignmentSection.section.section_type.id === 1
          || assignmentSection.section.section_type.id === 3)
        && !this.sectionControls[index].value) {
        message += `\n- ${assignmentSection.section.title}`;
      } else if (assignmentSection.section.section_type.id === 2
        && isNullOrUndefined(this.uploaders[assignmentSection.id].queue[0]) && isNullOrUndefined(assignmentSection.answer)) {
        message += `\n- ${assignmentSection.section.title}`;
      }
    });
    if (message !== '') {
      return confirm('Attention, les sections suivantes n\'ont pas étés remplies:' + message + '.\n\nVoulez-vous continuer quand même ?');
    }
    return true;
  }

  cancel(): void {
    if (this.authService.user.role.id === 2) {
      this.router.navigate(['/student/assignments', this.finished ? 'finished' : 'in-progress']);
    } else
    if (this.authService.user.role.id === 3) {
      this.router.navigate(['/teacher/statistics', this.assignment.evaluation.id, 'global']);
    }
    console.log(this.editors);
  }
  saveAssignment(draft: boolean = true): void {
    this.uploadErrors = this.formService.checkFiles(this.uploaders, this.filesSize, this.filesTypes);
    console.log(this.uploaders);
    if (!(this.uploadErrors.length > 0) && (draft || this.alertEmptySections())) {
      this.loaderService.display(true);
      this.applyFormData(draft);
      this.restService.updateDb('assignment', this._assignment)
        .then(response => {
          if (!response.success) {
          } else {
            this.assignmentForm.markAsPristine();
          }
          return response;
        })
        .then(response => {
          // EVAL
          this.getEntity(response.assignment);
          // FILES
          this.formService.upload(this.uploaders);
        })
        .then(() => {
          this.alertService.configWaitingAlert('Le devoir a bien été ' + (this.assignment.draft ? 'enregistré' : 'envoyé') + '.');
          this.loaderService.display(false);
        })
        .catch(() => {
          console.error(`Assignment \'${this.assignment.evaluation.name}\' cannot be changed in the database.`);
          this.alertService.configWaitingAlert('Une erreur est survenue...', 'error');
          this.loaderService.display(false);
        });
    }
  }

  // VIEWS
  displayInstructions() {
    this.view_instructions = true;
  }

  //
  getEntity(entity) {
    this.ngOnDestroy();
    this.assignment = new Assignment(entity);
    this.filterService.sortList(this.assignment.assignment_sections, 'section.order', true);
  }
  applyFormData(draft) {
    this._assignment = Object.assign({}, this.assignment);
    this._assignment.assignment_sections = [];
    this.assignment.assignment_sections.forEach(assignmentSection => {
      this._assignment.assignment_sections.push(assignmentSection);
      this._assignment.assignment_sections.find(assignmentSectionToFind => assignmentSectionToFind.section.order === assignmentSection.section.order).answer = this.sectionControls[assignmentSection.section.order - 1].value;
    });
    console.log(this._assignment.assignment_sections);
    this._assignment.draft = draft;
  }

}

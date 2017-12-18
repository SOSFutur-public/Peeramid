import { Injectable } from '@angular/core';
import { IMultiSelectSettings, IMultiSelectTexts } from 'angular-2-dropdown-multiselect';
import { FileType } from '../class/app.file.class';
import { FileUploader } from 'ng2-file-upload';
import { isUndefined } from 'ngx-bootstrap/bs-moment/utils/type-checks';

// Environment
import { environment } from '../../../environments/environment';

// Functions
import { joinDateAndTime, snakeCaseToCamelCase } from '../functions/app.core.utils.functions';

// Services
import { AppCoreLoaderService } from './app.core.loader.service';

// Validators
import {
  checkInteger, checkMax, checkMaxNumberOfValues, checkMin, checkMinNumberOfValues, checkRegex,
  checkSame
} from '../validator/app.core.validator';
import { checkMaxDate, checkMinDate } from '../validator/app.core.date.range.validator';

// -----

@Injectable ()
export class AppCoreFormService {

  constructor(
    private loaderService: AppCoreLoaderService
  ) {}
  // SELECT
  singleSelectSettings(search = true, all = false, none = false) {
    const settings: IMultiSelectSettings = {
      selectionLimit: 1,
      autoUnselect: true,
      enableSearch: search,
      checkedStyle: 'fontawesome',
      buttonClasses: 'btn btn-default multiselect',
      itemClasses: 'multiselect-item',
      showCheckAll: all,
      showUncheckAll: none,
      dynamicTitleMaxItems: 1000,
      displayAllSelectedText: false,
      closeOnSelect: true,
    };
    return settings;
  }
  multipleSelectSettings(search = true, all = false, none = false) {
    const settings: IMultiSelectSettings = {
      enableSearch: search,
      checkedStyle: 'fontawesome',
      buttonClasses: 'btn btn-default multiselect',
      itemClasses: 'multiselect-item',
      showCheckAll: all,
      showUncheckAll: none,
      dynamicTitleMaxItems: 1000,
      displayAllSelectedText: false,
    };
    return settings;
  }
  selectTexts(title = null) {
    const texts: IMultiSelectTexts = {
      checkAll: 'Sélectionner tout',
      uncheckAll: 'Désélectionner tout',
      checked: 'élément sélectionné',
      checkedPlural: 'éléments sélectionnés',
      searchPlaceholder: 'Rechercher...',
      searchEmptyResult: 'Aucun élément trouvé...',
      searchNoRenderText: 'Rechercher dans la barre de recherche...',
      defaultTitle: 'Sélectionner ' + title,
      allSelected: 'Tous les éléments sont sélectionnés',
    };
    return texts;
  }

  // WYSIWYG
  wysiwyg_settings(selector, read_only = false): any {
    const settings = {
      selector: '#wysiwyg-' + selector,
      language_url: environment.assets_path + '/assets/tinymce/langs/fr_FR.js',
      plugins: ['link', 'paste', 'table', 'autoresize', 'code', 'image', 'imagetools', 'textcolor', 'media', 'fullscreen', 'wordcount', 'advlist', 'lists'],
      skin_url: environment.assets_path +  '/assets/tinymce/skins/lightgray',
      menu: {},
      menubar: {},
      toolbar: [
        'undo redo | styleselect | code | fullscreen',
        'bold italic underline forecolor backcolor | alignleft aligncenter alignright | table | numlist bullist | link image media'
      ],
      branding: false,
      autoresize_bottom_margin: 0,
      autoresize_min_height: 250,
      paste_data_images: true,
      readonly : read_only,
    };
    return settings;
  }
  wysiwyg(editors, selector, control, read_only = false) {
    console.log('__construct__ CREATE WYSIWYG ' + selector);
    editors[selector] && editors[selector][0] ? console.log('suppression') : null;
    editors[selector] && editors[selector][0] ? editors[selector][0].remove() : null;
    editors[selector] = [];
    const wysiwyg_settings = this.wysiwyg_settings(selector, read_only);
    const wysiwyg_actions = {
      setup: editorTiny => {
        editors[selector][0] = editorTiny;
        editors[selector][0].on('keyup', () => {
          control.setValue(editors[selector][0].getContent());
          control.markAsDirty();
        });
        editors[selector][0].on('blur', () => {
          control.markAsTouched();
        });
        editors[selector][1] = control;
      },
      init_instance_callback : () => {
        console.log('WYSIWYG: ' + selector + ' is now initialized.');
      }
    };
    tinymce.init(Object.assign(wysiwyg_settings, wysiwyg_actions));
  }
  wysiwyg_remove(editors) {
    for (const key in editors) {
      console.log('__construct__ REMOVE WYSIWYG ' + key);
      if (editors[key] && editors[key][0]) {
        tinymce.remove(editors[key][0]);
      }
      console.log(editors[key] && editors[key][0] ? 'WYSIWYG supprimé.' : 'Pas de WYSIWYG à supprimer.');
    }
    return {};
  }

  // FILES
  setUniqueFileSize(uploaders: FileUploader[], maxSize: number): number[] {
    let maxQueue = 0;
    let filesSize: number[];

    uploaders.forEach(uploader => {
      maxQueue = ( uploader.queue.length > maxQueue ? uploader.queue.length : maxQueue );
    });
    filesSize = [];
    for (let i = 0; i < maxQueue; i++) {
      filesSize.push(maxSize);
    }
    return filesSize;
  }
  setUniqueFileTypes(uploaders: FileUploader[], fileTypes: FileType[]): FileType[][] {
    let maxQueue: number;
    let filesTypes: FileType[][];

    maxQueue = 0;
    uploaders.forEach(uploader => {
      maxQueue = ( uploader.queue.length > maxQueue ? uploader.queue.length : maxQueue );
    });
    filesTypes = [];
    for (let i = 0; i < maxQueue; i++) {
      filesTypes.push(fileTypes);
    }
    return filesTypes;
  }
  checkFileType(name: string, fileTypes: FileType[]): boolean {
    let splitName: string[];
    let splitLen: number;
    if (fileTypes.length === 0) {
      return true;
    }
    return fileTypes.some(function(fileType){
      splitName = name.split('.');
      splitLen = splitName.length;
      return fileType.type === splitName[splitLen - 1];
    });
  }
  checkFileSize(size: number, maxSize: number): boolean {
    return (size / 1000000) <= maxSize;
  }
  checkFiles(uploaders: FileUploader[], filesSize: number[], filesTypes?: FileType[][]): string[][] {
    let errors: string[][];

    errors = [];
    uploaders.forEach((uploader, uploaderIndex) => {
      uploader.queue.forEach((queue, queueIndex) => {
        if (!isUndefined(filesTypes) && filesTypes[uploaderIndex].length > 0 && !this.checkFileType(queue.file.name, filesTypes[uploaderIndex])) {
          if (isUndefined(errors[uploaderIndex])) {
            errors[uploaderIndex] = [];
          }
          errors[uploaderIndex][queueIndex] = 'Le fichier ne correspond pas aux types de fichiers autorisés';
          console.error(`File \'${queue.file.name}\' cannot be uploaded : TYPE ERROR`);
        } else if (!this.checkFileSize(queue.file.size, filesSize[uploaderIndex])) {
          if (isUndefined(errors[uploaderIndex])) {
            errors[uploaderIndex] = [];
          }
          errors[uploaderIndex][queueIndex] = 'Le fichier dépasse la taille maximale autorisée';
          console.error(`File \'${queue.file.name}\' cannot be uploaded : SIZE ERROR`);
        }
      });
    });
    return errors;
  }
  upload(uploaders: FileUploader[], url?: string): void {
    uploaders.forEach(uploader => {
      if (uploader.queue.length > 0) {
        this.loaderService.display(true);
      }
      uploader.queue.forEach(item => {
        if (!isUndefined(url)) {
          item.url = url;
        }
        item.upload();
      });
    });
  }

  // CHECKS
  checkBackErrors(controls, response: any[]) {
    const errors = {};
    const labels = {};
    // Parsing if []
    for (let j = 0; j < response.length; j++) {
      const i = response[j].field.indexOf('[');
      let label = false;
      if (i !== -1) {
        response[j].field = response[j].field.substr(0, i);
        label = true;
      }
      labels[snakeCaseToCamelCase(response[j].field)] = label ? response[j].value + ' : ' : '';
    }
    // Errors
    controls.forEach(key_control => {
      errors[key_control] = [];
      response.filter(error => snakeCaseToCamelCase(error.field) === key_control).forEach(e => {
        errors[key_control].push(labels[key_control] + e.message);
      });
    });
    //console.log(errors);
    return errors;
  }
  checkFrontErrors(controls) {
    const errors = {};
    const values = {};
    for (const key_control in controls) {
      let value = null;
      if (key_control === 'form' || isUndefined(controls[key_control][0]) || controls[key_control][0] === null) {
        value = null;
      } else if (Array.isArray(controls[key_control][0]) && key_control.substr(0, 4) === 'date') {
        value = joinDateAndTime(controls[key_control][0][0].value, controls[key_control][0][1].value);
      } else if (Array.isArray(controls[key_control][0]) && controls[key_control][0][0] === 'file') {
        value = controls[key_control][0];
      } else if (Array.isArray(controls[key_control][0])) {

      } else if (!isUndefined(controls[key_control][0].value)) {
        value =  controls[key_control][0].value;
      } else {
        value = controls[key_control][0];
        //value = null;
      }
      values[key_control] = value;
      errors[key_control] = [];
      for (const key_condition in controls[key_control][1]) {
        const split = key_condition.split(':');
        // CONDITIONS
        let conditionAnd;
        let conditionOr;
        if (controls[key_control][3] === null) {
          conditionAnd = true;
          conditionOr = true;
        } else {
          conditionAnd = true;
          conditionOr = true;
          const dependsAnd = controls[key_control][3].split('&&');
          const dependsOr = controls[key_control][3].split('||');
          const dependsPartsAnd = [];
          const dependsPartsOr = [];
          if (dependsOr.length > 1) {
            conditionOr = false;
            for (const part of dependsOr) {
              dependsPartsOr.push(part.split(':'));
            }
            if (dependsPartsOr.length > 0) {
              for (let p of dependsPartsOr) {
                if (values[p[0]] === null) {
                  values[p[0]] = 'null';
                }
                if (p[1].toString().substr(0, 1) === '!') {
                  if (values[p[0]].toString() !== p[1].toString().substr(1)) {
                    conditionOr = true;
                  }
                } else {
                  if (values[p[0]].toString() === p[1].toString()) {
                    conditionOr = true;
                  }
                }
              }
            }
          } else {
            for (const part of dependsAnd) {
              dependsPartsAnd.push(part.split(':'));
            }
            if (dependsPartsAnd.length > 0) {
              for (let p of dependsPartsAnd) {
                if (values[p[0]] === null) {
                  values[p[0]] = 'null';
                }
                if (p[1].toString().substr(0, 1) === '!') {
                  if (values[p[0]].toString() === p[1].toString().substr(1)) {
                    conditionAnd = false;
                  }
                } else {
                  if (values[p[0]].toString() !== p[1].toString()) {
                    conditionAnd = false;
                  }
                }
              }
            }
          }
        }
        if (conditionAnd && conditionOr) {
          switch (split[0]) {
            case 'dirty':
              if (controls[key_control][0].dirty) {
                errors[key_control].push(controls[key_control][1][key_condition]);
              }
              break;
            case 'true':
              errors[key_control].push(controls[key_control][1][key_condition]);
              break;
            case 'required':
              if (isUndefined(value) || value === null || value === '' || (typeof value === 'object' && value.length === 0)) {
                errors[key_control].push(controls[key_control][1][key_condition]);
              }
              break;
            case 'min':
              if (!checkMin(isUndefined(values[split[1]]) ? +split[1] : values[split[1]], value)) {
                errors[key_control].push(controls[key_control][1][key_condition]);
              }
              break;
            case 'max':
              if (!checkMax(isUndefined(values[split[1]]) ? +split[1] : values[split[1]], value)) {
                errors[key_control].push(controls[key_control][1][key_condition]);
              }
              break;
            case 'maxNumber':
              if (!checkMaxNumberOfValues(+split[1], value)) {
                errors[key_control].push(controls[key_control][1][key_condition]);
              }
              break;
            case 'minNumber':
              if (!checkMinNumberOfValues(+split[1], value)) {
                errors[key_control].push(controls[key_control][1][key_condition]);
              }
              break;
            case 'integer':
              if (!checkInteger(value)) {
                errors[key_control].push(controls[key_control][1][key_condition]);
              }
              break;
            case 'minDate':
              const datesMin = split[1].split(',');
              const dMin = [];
              datesMin.forEach(date => {
                if (date === 'now') {
                  dMin.push(new Date());
                } else {
                  dMin.push(values[date]);
                }
              });
              if (!checkMinDate(dMin)) {
                errors[key_control].push(controls[key_control][1][key_condition]);
              }
              break;
            case 'maxDate':
              const datesMax = split[1].split(',');
              const dMax = [];
              datesMax.forEach(date => {
                if (date === 'now') {
                  dMax.push(new Date());
                } else {
                  dMax.push(values[date]);
                }
              });
              if (!checkMaxDate(dMax)) {
                errors[key_control].push(controls[key_control][1][key_condition]);
              }
              break;
            case 'fileType':
              value[1].queue.forEach((file, index) => {
                if (!this.checkFileType(file.file.name, value[3])) {
                  errors[key_control][index] = controls[key_control][1][key_condition];
                }
              });
              break;
            case 'fileSize':
              value[1].queue.forEach((file, index) => {
                if (!this.checkFileSize(file.file.size, value[2])) {
                  errors[key_control][index] = controls[key_control][1][key_condition];
                }
              });
              break;
            case 'regex':
              if (!checkRegex(split[1], value)) {
                errors[key_control].push(controls[key_control][1][key_condition]);
              }
              break;
            case 'same':
              if (!checkSame(isUndefined(values[split[1]]) ? +split[1] : values[split[1]], value)) {
                errors[key_control].push(controls[key_control][1][key_condition]);
              }
              break;
            default:
              break;
          }
        }
      }
    }
    //console.log(errors);
    return errors;
  }
  checkEmptyChecks(checks) {
    for (const key in checks) {
      if (checks[key].length > 0) {
        return false;
      }
    }
    return true;
  }
  checksToArray(checks: {}, controls: {}) {
    const response = [];
    for (const key in checks) {
      if (checks[key].length > 0) {
        response.push(controls[key][2]);
      }
    }
    return response;
  }

}

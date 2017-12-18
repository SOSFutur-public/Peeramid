import { Injectable } from '@angular/core';
import { RequestOptions, Headers, ResponseContentType } from '@angular/http';
import { AuthHttp } from 'angular2-jwt';
import 'rxjs/Rx' ;
import 'rxjs/add/operator/toPromise';
import { Observable } from 'rxjs/Observable';
import { isUndefined } from 'ngx-bootstrap/bs-moment/utils/type-checks';

// Environment
import { environment } from '../../../environments/environment';

// Classes
import { Lesson } from '../../lessons/class/app.lesson.class';

// Services
import { AppCoreLoaderService } from './app.core.loader.service';

// -----

@Injectable ()
export class AppCoreRestService {

  url = {
    login                          :   { class : 'Auth',               url : ['login']                                  },
    loggedIn                       :   { class : 'Auth',               url : ['users/loggedIn']                         },
    groups                         :   { class : 'Group',              url : ['groups']                                 },
    group_image                    :   { class : 'Group',              url : ['groups', 'image']                        },
    group                          :   { class : 'Group',              url : ['group']                                  },
    students                       :   { class : 'User',               url : ['users/students']                         },
    student                        :   { class : 'User',               url : ['users']                                  },
    teachers                       :   { class : 'User',               url : ['users/teachers']                         },
    teacher                        :   { class : 'User',               url : ['users']                                  },
    users                          :   { class : 'User',               url : ['users']                                  },
    user                           :   { class : 'User',               url : ['user']                                   },
    userProfile                    :   { class : 'User',               url : ['user/profile']                           },
    userImage                      :   { class : 'User',               url : ['users', 'image']                         },
    userLessons                    :   { class : 'Lesson',             url : ['user/lessons']                           },
    userGroups                     :   { class : 'Group',              url : ['user/groups']                            },
    userAssignments                :   { class : 'Assignment',         url : ['assignments/list']                       },
    assignments                    :   { class : 'Assignment',         url : ['assignments']                            },
    assignment                     :   { class : 'Assignment',         url : ['assignment']                             },
    assignmentCorrections          :   { class : 'Assignment',         url : ['assignments', 'corrections']             },
    sectionTypes                   :   { class : 'SectionType',        url : ['sectiontypes']                           },
    assignmentSections             :   { class : 'AssignmentSection',  url : ['assignmentsections']                     },
    assignmentSection              :   { class : 'AssignmentSection',  url : ['assignmentsection']                      },
    assignmentSection_files        :   { class : 'AssignmentSection',  url : ['assignmentsections', 'files']            },
    correctionCriterion            :   { class : 'CorrectionCriteria', url : ['correctioncriterion']                    },
    correctionCriteria             :   { class : 'CorrectionCriteria', url : ['correctioncriteria']                     },
    userCorrections                :   { class : 'Correction',         url : ['corrections/list']                       },
    corrections                    :   { class : 'Correction',         url : ['corrections']                            },
    correction                     :   { class : 'Correction',         url : ['correction']                             },
    correctionOpinion              :   { class : 'Opinion',            url : ['correctionopinion']                      },
    correctionsOpinions            :   { class : 'Correction',         url : ['corrections', 'opinions']                },
    correctionAttributions         :   { class : 'Correction',         url : ['evaluations', 'attribution']             },
    correctionAttributionsReset    :   { class : 'Correction',         url : ['evaluation/reset-attribution']           },
    correctionSections             :   { class : 'Correction',         url : ['correctionsections']                     },
    correctionSection              :   { class : 'Correction',         url : ['correctionsection']                      },
    evaluationsList                :   { class : 'Evaluation',         url : ['evaluations/list']                       },
    evaluations                    :   { class : 'Evaluation',         url : ['evaluations']                            },
    evaluation                     :   { class : 'Evaluation',         url : ['evaluation']                             },
    evaluationCorrection           :   { class : 'Evaluation',         url : ['evaluation/correction']                  },
    evaluationStat                 :   { class : 'Evaluation',         url : ['evaluation/stats']                       },
    evaluationStats                :   { class : 'Evaluation',         url : ['evaluations', 'stats']                   },
    evaluationStatsCriterias       :   { class : 'Evaluation',         url : ['evaluations', 'stats/criterias']         },
    evaluationStatsReliability     :   { class : 'Evaluation',         url : ['evaluations', 'quality']                 },
    evaluationStatTrapezium        :   { class : 'Trapezium',          url : ['criteria/trapezium']                     },
    evaluationStatsTrapezium       :   { class : 'Evaluation',         url : ['criterias', 'trapezium']                 },
    evaluationStatsCharts          :   { class : 'Evaluation',         url : ['evaluations', 'charts']                  },
    evaluationDuplication          :   { class : 'Evaluation',         url : ['evaluations', 'duplicates']              },
    evaluationArchiving            :   { class : 'Evaluation',         url : ['evaluation/archive']                     },
    evaluation_subject_files       :   { class : 'Evaluation',         url : ['evaluations', 'subjects']                },
    evaluation_example_assignments :   { class : 'Evaluation',         url : ['evaluations', 'examples']                },
    evaluation_toggle              :   { class : 'Evaluation',         url : ['evaluation/activate/assignments']        },
    evaluation_subject             :   { class : 'Evaluation',         url : ['evaluations', 'subject']                 },
    correction_toggle              :   { class : 'Evaluation',         url : ['evaluation/activate/corrections']        },
    criteriaTypes                  :   { class : 'CriteriaType',       url : ['criteriatypes']                          },
    fileTypes                      :   { class : 'FileType',           url : ['filetypes']                              },
    imagesFileTypes                :   { class : 'FileType',           url : ['filetypes/images']                       },
    csvFileTypes                   :   { class : 'FileType',           url : ['filetypes/csv']                          },
    markModes                      :   { class : 'MarkMode',           url : ['markmodes']                              },
    markPrecisionModes             :   { class : 'MarkMode',           url : ['markprecisionmodes']                     },
    markRoundModes                 :   { class : 'MarkMode',           url : ['markroundmodes']                         },
    lessons                        :   { class : 'Lesson',             url : ['lessons']                                },
    lesson_image                   :   { class : 'Lesson',             url : ['lessons', 'image']                       },
    lesson                         :   { class : 'Lesson',             url : ['lesson']                                 },
    lessonTeacher                  :   { class : 'Lesson',             url : ['lessons', 'teacher']                     },
    lessonEvaluations              :   { class : 'Evaluation',         url : ['lessons', 'evaluations']                 },
    lessonAssignments              :   { class : 'Assignment',         url : ['assignments/lessons']                    },
    lessonCorrections              :   { class : 'Correction',         url : ['corrections/lessons']                    },
    categories                     :   { class : 'Category',           url : ['categories']                             },
    category                       :   { class : 'Category',           url : ['category']                               },
    settings                       :   { class : 'Setting',            url : ['settings']                               },
    setting                        :   { class : 'Setting',            url : ['setting']                                },
    exportStats                    :   { class : '',                   url : ['exports']                                },
    maxSizeSetting                 :   { class : 'Setting',            url : ['settings/1']                             },
    error                          :   { class : '',                   url : ['errors']                                 }
  };

  constructor(
    private authHttp: AuthHttp,
    private loaderService: AppCoreLoaderService
  ) {}

  // Error
  handleError(error: any): Promise<any> {
    console.error('Erreur : ', error);
    this.loaderService.display(false);
    return Promise.reject(error.message || error);
  }

  // GET Promise
  getDb(key: string, ids?: [any], option?: string): Promise<any> {
    let url: string = environment.api_url;

    for (let i = 0; i < this.url[key]['url'].length; i++) {
      if (i !== 0) {
        url += '/';
      }
      url += this.url[key]['url'][i] + ((isUndefined(ids) || ids === null || ids.length <= i) ? '' : `/${ids[i]}`);
    }
    url += isUndefined(option) ? '' : `/${option}`;
    console.log(`GET : ${url}`);
    return this.authHttp.get(url)
      .toPromise()
      .then(response => response.json())
      .catch(error => this.handleError(error));
  }
  // UPDATE Promise
  updateDb(key: string, body: any): Promise<any> {
    let url: string;
    let headers: Headers;
    let options: RequestOptions;

    url = environment.api_url + this.url[key]['url'];
    headers = new Headers();
    headers.append('Content-Type', 'application/json');
    options = new RequestOptions({ headers: headers });
    console.log(`UPDATE : ${url}`);
    return this.authHttp.put(url, JSON.stringify(body), options)
      .toPromise()
      .then(response => response.json())
      .catch(this.handleError);
  }
  // POST Promise
  addDb(key: string, body: any): Promise<any> {
    let url: string;
    let headers: Headers;
    let options: RequestOptions;

    url = environment.api_url + this.url[key]['url'];
    headers = new Headers();
    headers.append('Content-Type', 'application/json');
    options = new RequestOptions({ headers: headers });
    console.log(`POST : ${url}`);
    return this.authHttp.post(url, JSON.stringify(body), options)
      .toPromise()
      .then(response => response.json())
      .catch(this.handleError);
  }
  // DELETE Promise
  deleteDb(key: string, ids?: [number]): Promise<any> {
    let url: string = environment.api_url;
    for (let i = 0; i < this.url[key]['url'].length; i++) {
      if (i !== 0) {
        url += '/';
      }
      url += this.url[key]['url'][i] + ((isUndefined(ids) || ids.length <= i) ? '' : `/${ids[i]}`);
    }
    console.log(`DELETE : ${url}`);
    return this.authHttp.delete(url)
      .toPromise()
      .then(response => response.json())
      .catch(this.handleError);
  }
  // DUPLICATE Promise
  duplicateDb(key: string, ids?: [number]): Promise<any> {
    let url: string = environment.api_url;

    for (let i = 0; i < this.url[key]['url'].length; i++) {
      if (i !== 0) {
        url += '/';
      }
      url += this.url[key]['url'][i] + ((isUndefined(ids) || ids.length <= i) ? '' : `/${ids[i]}`);
    }

    console.log(`DUPLICATE : ${url}`);
    return this.authHttp.post(url, null)
      .toPromise()
      .then(response => response.json())
      .catch(this.handleError);
  }
  // GET File Observable
  getFile(key: string, ids?: [any]): Observable<Blob> {
    let url: string = environment.api_url + 'files/';
    let headers: Headers;
    let options: RequestOptions;

    for (let i = 0; i < this.url[key]['url'].length; i++) {
      if (i !== 0) {
        url += '/';
      }
      url += this.url[key]['url'][i] + ((isUndefined(ids) || ids.length <= i) ? '' : `/${ids[i]}`);
    }
    headers = new Headers();
    options = new RequestOptions({ headers: headers, responseType: ResponseContentType.ArrayBuffer });
    console.log(url);
    return this.authHttp.get(url, options)
      .map(res => res.blob())
      .catch(this.handleError);
  }
  // DOWNLOAD File Observable
  downloadFile(key: string, ids?: [any]): Observable<Blob> {
    let url: string = environment.api_url;
    let headers: Headers;
    let options: RequestOptions;

    for (let i = 0; i < this.url[key]['url'].length; i++) {
      if (i !== 0) {
        url += '/';
      }
      url += this.url[key]['url'][i] + ((isUndefined(ids) || ids.length <= i) ? '' : `/${ids[i]}`);
    }
    headers = new Headers();
    headers.append('Accept', 'application/zip');
    options = new RequestOptions({ headers: headers, responseType: ResponseContentType.Blob });
    return this.authHttp.get(url, options)
      .map(res => res.blob())
      .catch(this.handleError);
  }

}

// authentication/authentication.service.ts
import { Injectable } from '@angular/core';

import { Http, Response, Headers, RequestOptions } from '@angular/http';
import { tokenNotExpired } from 'angular2-jwt';

import 'rxjs/add/operator/map';
import { environment } from '../../../environments/environment';
import { Router } from '@angular/router';
import { User } from '../../users/class/app.user.class';
import { AppCoreRestService } from '../../core/service/app.core.rest.service';
import { AppCoreAlertService } from '../../core/service/app.core.alert.service';

@Injectable()
export class AppAuthAuthenticationService {

  user: User = null;
  userImage;
  url = {
    login                 :   { class : 'Auth',               url : ['login']                                  },
    request               :   { class : 'Auth',               url : ['users/request-password']                 },
    reset                 :   { class : 'Auth',               url : ['users/reset-password']                   },
  };

  constructor(
    private http: Http,
    private router: Router,
    private restService: AppCoreRestService,
    private alertService: AppCoreAlertService,
  ) {}

  // Error
  handleError(error: any): Promise<any> {
    console.error('Erreur : ', error);
    return Promise.reject(error.message || error);
  }

  post(key: string, body: any): Promise<any> {
    let url: string;
    let headers: Headers;
    let options: RequestOptions;

    url = environment.api_url + this.url[key]['url'];
    headers = new Headers();
    headers.append('Content-Type', 'application/json');
    options = new RequestOptions({ headers: headers });

    return this.http.post(url, JSON.stringify(body), options)
      .toPromise()
      .then(response => response.json())
      .catch(this.handleError);
  }
  logout(redirection: boolean = false) {
    localStorage.removeItem('peeramid_token');
    this.user = null;
    if (redirection) {
      this.router.navigate(['/login']);
    }
  }
  loggedIn(redirection: boolean = false) {
    if (!tokenNotExpired('peeramid_token') && redirection) {
      this.logout(true);
    }
    return tokenNotExpired('peeramid_token');
  }
  getLoggedUser(): Promise<any> {
    console.log('CHECK LOGGED USER');
    return this.restService.getDb('loggedIn')
      .then(
        user => {
          this.user = new User(user);
        })
      .catch(this.handleError);
  }
  getToken() {
    return localStorage.getItem('peeramid_token');
  }
  checkRole(roles: string[], redirection = false): boolean {
    if (!this.loggedIn() && redirection) {
      this.logout(true);
    } else if (this.user === null) {
      return false;
    } else {
      let response = false;
      roles.forEach(role => {
        if (role === 'admin' && this.user.role.id === 1) {
          response = true;
        } else if (role === 'teacher' && this.user.role.id === 3) {
          response = true;
        } else if (role === 'student' && this.user.role.id === 2 ) {
          response = true;
        }
      });
      if (!response && redirection) {
        this.router.navigate(['/home']);
        this.alertService.setAlert('Vous n\'avez pas accès à cette page', 'error');
      } else {
        return response;
      }
    }
  }
}

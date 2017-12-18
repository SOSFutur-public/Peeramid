import { Component, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';

// Classes
import { User } from '../../class/app.user.class';

// Animations
import { routerAnimation } from '../../../../animations/router.animations';

// Services
import { AppCoreRestService } from '../../../core/service/app.core.rest.service';
import { AppAuthAuthenticationService } from '../../../auth/service/app.auth.authentication.service';
import { AppCoreLoaderService } from '../../../core/service/app.core.loader.service';

// -----

@Component ({
  selector: 'app-teacher-edit',
  templateUrl: '../html/app.teacher.edit.component.html',
  animations: [routerAnimation],
  host: { '[@routerAnimation]': '' }
})
export class AppTeacherEditComponent implements OnInit {

  editTeacher: User;
  dirty: boolean;

  constructor(
    private route: ActivatedRoute,
    private restService: AppCoreRestService,
    private authService: AppAuthAuthenticationService,
    private loaderService: AppCoreLoaderService
  ) {
    this.authService.checkRole(['admin'], true);
  }

  ngOnInit(): void {
    let id: number;

    id = +this.route.snapshot.params['id'];
    this.getTeacher(id);
  }

  canDeactivate(): boolean {
    if (this.dirty) {
      return confirm('Êtes-vous sûr de quitter le formulaire sans enregistrer vos modifications?');
    }
    return true;
  }

  getTeacher(id: number): void {
    this.loaderService.display(true);
    this.restService.getDb('teacher', [id])
      .then(teachers => this.editTeacher = new User(teachers))
      .then(() => this.loaderService.display(false));
  }

}

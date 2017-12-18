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
  selector: 'app-student-edit',
  templateUrl: '../html/app.student.edit.component.html',
  animations: [routerAnimation],
  host: { '[@routerAnimation]': '' }
})
export class AppStudentEditComponent implements OnInit {

  editStudent: User;
  dirty: boolean;

  constructor(
    private route: ActivatedRoute,
    private restService: AppCoreRestService,
    private authService: AppAuthAuthenticationService,
    private loaderService: AppCoreLoaderService
  ) {
    console.log('__CONSTRUCT__ app.student.edit.component');
    this.authService.checkRole(['admin'], true);
  }

  ngOnInit(): void {
    let id: number;

    id = +this.route.snapshot.params['id'];
    this.getStudent(id);
  }

  canDeactivate(): boolean {
    if (this.dirty) {
      return confirm('Êtes-vous sûr de quitter le formulaire sans enregistrer vos modifications?');
    }
    return true;
  }

  getStudent(id: number): void {
    this.loaderService.display(true);
    this.restService.getDb('student', [id])
      .then(student => this.editStudent = new User(student))
      .then(() => this.loaderService.display(false));
  }

}

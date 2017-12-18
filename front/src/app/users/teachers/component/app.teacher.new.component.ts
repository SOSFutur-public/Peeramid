import { Component, OnInit } from '@angular/core';

// Classes
import { User } from '../../class/app.user.class';
import { Role } from '../../class/app.role.class';

// Animations
import { routerAnimation } from '../../../../animations/router.animations';

// Services
import { AppAuthAuthenticationService } from '../../../auth/service/app.auth.authentication.service';

// -----

@Component ({
  selector: 'app-teacher-new',
  templateUrl: '../html/app.teacher.new.component.html',
  animations: [routerAnimation],
  host: { '[@routerAnimation]': '' }
})
export class AppTeacherNewComponent implements OnInit {

  newTeacher: User;
  dirty: boolean;

  constructor(
    private authService: AppAuthAuthenticationService
  ) {
    this.authService.checkRole(['admin'], true);
  }

  ngOnInit(): void {
    this.newTeacher = new User();
    this.newTeacher.role = new Role({id: 3, title: 'Teacher'});
  }

  canDeactivate(): boolean {
    if (this.dirty) {
      return confirm('Êtes-vous sûr de quitter le formulaire sans enregistrer vos modifications?');
    }
    return true;
  }

}

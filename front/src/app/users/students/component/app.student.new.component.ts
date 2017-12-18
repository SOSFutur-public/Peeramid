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
  selector: 'app-student-new',
  templateUrl: '../html/app.student.new.component.html',
  animations: [routerAnimation],
  host: { '[@routerAnimation]': '' }
})
export class AppStudentNewComponent implements OnInit {

  newStudent: User;
  dirty: boolean;

  constructor(
    private authService: AppAuthAuthenticationService
  ) {
    this.authService.checkRole(['admin'], true);
  }

  ngOnInit(): void {
    this.newStudent = new User();
    this.newStudent.role = new Role({id: 2, title: 'Student'});
  }

  canDeactivate(): boolean {
    if (this.dirty) {
      return confirm('Êtes-vous sûr de quitter le formulaire sans enregistrer vos modifications?');
    }
    return true;
  }

}

import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
import { FormsModule } from '@angular/forms';
import { AngularFontAwesomeModule } from 'angular-font-awesome/angular-font-awesome';
import { BsDropdownModule } from 'ngx-bootstrap';

// Modules
import { AppCoreFormsModule } from '../core/app.core.forms.module';
import { AppGroupsRoutingModule } from './app.groups.routing.module';

// Components
import { AppGroupsAdminComponent } from './component/app.groups.admin.component';
import { AppGroupsStudentComponent } from './component/app.groups.student.component';
import { AppGroupEditComponent } from './component/app.group.edit.component';
import { AppGroupNewComponent } from './component/app.group.new.component';

// -----

@NgModule ({
  imports: [
    CommonModule,
    FormsModule,
    AppGroupsRoutingModule,
    NgbModule,
    AngularFontAwesomeModule,
    BsDropdownModule,
    AppCoreFormsModule
  ],
  declarations: [
    AppGroupsAdminComponent,
    AppGroupsStudentComponent,
    AppGroupEditComponent,
    AppGroupNewComponent
  ]
})
export class AppGroupsModule {}

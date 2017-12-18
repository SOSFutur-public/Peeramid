import { Component, Input, OnInit } from '@angular/core';

// Classes
import { Group } from '../class/app.group.class';

// Animations
import { routerAnimation } from '../../../animations/router.animations';

// Services
import { AppCoreFilterService } from '../../core/service/app.core.filter.service';
import { AppCoreRestService } from '../../core/service/app.core.rest.service';
import { AppAuthAuthenticationService } from '../../auth/service/app.auth.authentication.service';
import { AppCoreLoaderService } from '../../core/service/app.core.loader.service';

// -----

@Component ({
  selector: 'app-groups-admin',
  templateUrl: '../html/app.groups.admin.component.html',
  animations: [routerAnimation],
  host: { '[@routerAnimation]': '' }
})
export class AppGroupsAdminComponent implements OnInit {

  @Input() searchBox: string;
  groups: Group[] = [];
  sortParams: { column: string, ascendOrder: boolean };
  searchLabel: { name: string, property: string };

  constructor(
    private authService: AppAuthAuthenticationService,
    private loaderService: AppCoreLoaderService,
    private restService: AppCoreRestService,
    private filterService: AppCoreFilterService
  ) {
    console.log('__CONSTRUCT__ app.group.admin.component');
    this.authService.checkRole(['admin'], true);
  }

  ngOnInit(): void {
    this.searchBox = '';
    this.searchLabel = { name: 'Nom', property: 'name' };
    this.sortParams = { column: null, ascendOrder: true};
    this.getGroups();
  }

  getGroups(): void {
    this.loaderService.display(true);
    this.restService.getDb('groups')
      .then(groups => {
        for (const group of groups) {
          this.groups.push(new Group(group));
        }
      })
      .then(() => {
        console.log(this.groups);
        this.loaderService.display(false);
      });
  }

  isSelected(column: string, ascendOrder: boolean) {
    if (column === this.sortParams.column && ascendOrder === this.sortParams.ascendOrder) {
      return ' active';
    }
    return '';
  }

  sortGroups(property: string, column: string, ascendOrder: boolean): void {
    this.sortParams.ascendOrder = ascendOrder;
    this.sortParams.column = column;
    this.filterService.sortList(this.groups, property, this.sortParams.ascendOrder);
  }

  deleteGroup(group: Group): void {
    let index: number;

    if (confirm(`Etes vous sûr de vouloir supprimer le groupe \'${group.name}\'?`)) {
      this.loaderService.display(true);
      this.restService.deleteDb('groups', [group.id])
        .then(() => {
          index = this.groups.indexOf(group);
          this.groups.splice(index, 1);
          this.loaderService.display(false);
        })
        .catch(() => {
          console.error(`Group \'${group.name}\', id: ${group.id}, cannot be deleted in the database.`);
          alert(`Une erreur est survenue durant la suppression du groupe \'${group.name}\'.`);
          this.loaderService.display(false);
        });
    }
  }

  matchingSearch(object: any, propriety: string) {
    let str: string;

    str = this.filterService.handleMultipleProperties(object, propriety);
    return this.filterService.matchingStrings(str, this.searchBox);
  }

}

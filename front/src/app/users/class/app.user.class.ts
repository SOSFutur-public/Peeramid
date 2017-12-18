// Environment
import { environment } from '../../../environments/environment';

// Classes
import { Role } from './app.role.class';
import { Lesson } from '../../lessons/class/app.lesson.class';
import { Group } from '../../groups/class/app.group.class';
import { Assignment } from '../../evaluations/class/app.evaluation.class';

// Functions
import { imgExists } from '../../core/functions/app.core.utils.functions';

// -----

export class User {

  id: number = null;
  first_name: string = null;
  last_name: string = null;
  username: string = null;
  email: string = null;
  password: string = null;
  role: Role = null;
  image: string = null;
  code?: string = null;
  assignments?: Assignment[] = [];
  lessons: Lesson[] = [];
  groups?: Group[] = [];

  path = 'users';

  constructor(data = null) {
    if (data !== null) {
      this.id = data['id'];
      this.first_name = data['first_name'];
      this.last_name = data['last_name'];
      this.username = data['username'];
      this.email = data['email'];
      this.password = data['password'];
      this.role = new Role(data['role']);
      this.image = data['image'] !== undefined ? data['image'] : null;
      this.groups = data['groups'] !== undefined ? data['groups'] : [];
      this.lessons = data['lessons'] !== undefined ? data['lessons'] : [];
    }
  }

  getLessonsId() {
    const ids = [];
    for (const lesson of this.lessons) {
      ids.push(lesson.id);
    }
    return ids;
  }
  getGroupsId() {
    const ids = [];
    for (const group of this.groups) {
      ids.push(group.id);
    }
    return ids;
  }
  name(inverse = false) {
    if (inverse) {
      return this.first_name + ' ' + this.last_name;
    }
    return this.last_name + ' ' + this.first_name;
  }
  displayImage() {
    return (this.image !== null && this.image !== undefined && imgExists(environment.upload_url + this.path + '/' + this.id + '/' + this.image)) ? environment.upload_url + this.path + '/' + this.id + '/' + this.image : environment.assets_path + '/assets/img/default/user.png';
  }
  displayDefaultImage() {
    return environment.assets_path + '/assets/img/default/user.png';
  }

}

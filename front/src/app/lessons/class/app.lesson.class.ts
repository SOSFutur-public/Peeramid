import { isUndefined } from 'ngx-bootstrap/bs-moment/utils/type-checks';

// Environment
import { environment } from '../../../environments/environment';

// Classes
import { Category } from './app.category.class';
import { User } from '../../users/class/app.user.class';
import { Group } from '../../groups/class/app.group.class';

// Functions
import { getDate } from '../../core/functions/app.core.utils.functions';

// -----

export class Lesson {
  id: number = null;
  name: string = null;
  description: string = null;
  category: Category = null;
  users: User[] = [];
  groups: Group[] = [];
  image: string = null;
  evaluations: {
    id: number,
    name: string,
    date_end_assignment: Date,
    date_end_correction: Date,
    mark: number,
    teacher: User
  }[] = [];

  path = 'lessons';

  constructor(data?: any) {
    if (!isUndefined(data)) {
      this.id = data['id'];
      this.name = data['name'];
      this.description = data['description'];
      this.category = new Category(data['category']);
      if (!isUndefined(data['users'])) {
        data['users'].forEach(user => this.users.push(new User(user)));
      }
      if (!isUndefined(data['groups'])) {
        data['groups'].forEach(group => this.groups.push(new Group(group)));
      }
      this.image = !isUndefined(data['image']) ? data['image'] : null;
      if (!isUndefined(data['evaluations'])) {
        data['evaluations'].forEach(evaluation => this.evaluations.push({
          id: evaluation.id,
          name: evaluation.name,
          date_end_assignment: getDate(evaluation.date_end_assignment),
          date_end_correction: getDate(evaluation.date_end_correction),
          mark: evaluation.mark,
          teacher: new User(evaluation.teacher)
      }));
      }
    }
  }

  getCategoryId() {
    return this.category !== null ? [this.category.id] : null;
  }
  getStudents() {
    const students = [];
    for (const user of this.users) {
      user.role.id === 2 ? students.push(user) : null;
    }
    return students;
  }
  getStudentsId() {
    const ids = [];
    for (const user of this.users) {
      user.role.id === 2 ? ids.push(user.id) : null;
    }
    return ids;
  }
  getTeachers() {
    const teachers = [];
    for (const user of this.users) {
      user.role.id === 3 ? teachers.push(user) : null;
    }
    return teachers;
  }
  getTeachersId() {
    const ids = [];
    for (const user of this.users) {
      user.role.id === 3 ? ids.push(user.id) : null;
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
  displayImage() {
    return this.image !== null ? environment.upload_url + this.path + '/' + this.id + '/' + this.image : environment.assets_path + '/assets/img/default/lesson.png';
  }
  displayDefaultImage() {
    return environment.assets_path + '/assets/img/default/lesson.png';
  }
}

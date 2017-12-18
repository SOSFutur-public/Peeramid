// Classes
import { Lesson } from '../../lessons/class/app.lesson.class';
import { User } from '../../users/class/app.user.class';

export class Group {

  id: number = null;
  name: string = null;
  lessons: Lesson[] = [];
  users: User[] = [];

  constructor(data = null) {
    if (data !== null) {
      this.id = data['id'];
      this.name = data['name'];
      this.lessons = data['lessons'];
      this.users = data['users'];
    }
  }

  getUsersId() {
    const ids = [];
    for (const user of this.users) {
      ids.push(user.id);
    }
    return ids;
  }
  getLessonsId() {
    const ids = [];
    for (const lesson of this.lessons) {
      ids.push(lesson.id);
    }
    return ids;
  }

}

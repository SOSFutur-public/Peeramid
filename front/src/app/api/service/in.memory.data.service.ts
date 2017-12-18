import { InMemoryDbService } from 'angular-in-memory-web-api';
import { USERS } from '../data/app.users.data';
import { ASSIGNMENTS } from '../data/app.assignments.data';
import { LESSONS } from '../data/app.lessons.data';
import { GROUPS } from '../data/app.groups.data';

export class InMemoryDataService implements InMemoryDbService {
  createDb() {
    const users = USERS;
    const groups = GROUPS;
    const assignments = ASSIGNMENTS;
    const lessons = LESSONS;
    return { users, groups, assignments, lessons };
  }
}

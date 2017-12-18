export class Role {

  id: number;
  title: string;
  url = {1 : 'Admin', 2 : 'Student', 3 : 'Teacher'};
  roles = {1 : 'Admin', 2 : 'Étudiant', 3 : 'Professeur'};

  constructor(data = null) {
    if (data !== null) {
      this.id = data['id'];
      this.title = data['title'];
    }
  }

  getUrl() {
    return this.url[this.id].toLowerCase();
  }

  getRole() {
    return this.roles[this.id];
  }

  checkRole(role): boolean {
    let response = false;
    if (role === 'admin') {
      response = this.id === 1;
    } else if (role === 'teacher') {
      response = this.id === 3;
    } else if (role === 'student') {
      response = this.id === 2;
    }
    return response;
  }

}

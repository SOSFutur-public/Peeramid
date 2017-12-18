export class Category {

  id: number = null;
  name: string = null;
  image: string = null;

  constructor(data = null) {
    if (data !== null) {
      this.id = data['id'];
      this.name = data['name'];
      this.image = data['image'];
    }
  }

}

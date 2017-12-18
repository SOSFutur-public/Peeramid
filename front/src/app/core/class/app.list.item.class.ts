export class ListItem {
  id: number;
  selected: boolean;
}

export class SelectionList {
  name: string;
  list: ListItem[];
}

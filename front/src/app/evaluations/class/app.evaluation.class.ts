import { isUndefined } from 'ngx-bootstrap/bs-moment/utils/type-checks';

// Classes
import { User } from '../../users/class/app.user.class';
import { Group } from '../../groups/class/app.group.class';
import { Lesson } from '../../lessons/class/app.lesson.class';
import { FileType } from '../../core/class/app.file.class';

// Functions
import { getDate } from '../../core/functions/app.core.utils.functions';
import {isNullOrUndefined} from "util";


export class Evaluation {
  id: number = null;
  name: string = null;
  subject: string = null;
  subject_files: string[] = [];
  date_start_assignment: Date = null;
  date_end_assignment: Date = null;
  date_start_correction: Date = null;
  date_end_correction: Date = null;
  date_end_opinion: Date = null;
  number_corrections: number = null;
  anonymity: boolean = true;
  individual_assignment: boolean = true;
  individual_correction: boolean = true;
  date_creation: Date = null;
  assignment_instructions: string = null;
  correction_instructions: string = null;
  example_assignments: string[] = [];
  active_assignment: boolean = false;
  active_correction: boolean = false;
  assignment_average: number = null;
  show_assignment_mark: boolean = true;
  archived: boolean = null;
  mark_round_mode: MarkMode = null;
  mark_precision_mode: MarkMode = null;
  use_teacher_mark: boolean = false;
  mark_mode: MarkMode = null;
  teacher: User = null;
  lesson: Lesson = null;
  users: User[] = [];
  groups: Group[] = [];
  assignments: Assignment[] = [];
  sections: Section[] = [];

  constructor(data?: any, teacher?: boolean) {
    if (!isUndefined(data)) {
      this.id = data['id'];
      this.name = data['name'];
      this.subject = data['subject'];
      if (!isUndefined(data['subject_files'])) {
        data['subject_files'].forEach(subject_file => this.subject_files.push(subject_file));
      }
      this.date_start_assignment = getDate(data['date_start_assignment']);
      this.date_end_assignment = getDate(data['date_end_assignment']);
      this.date_start_correction = getDate(data['date_start_correction']);
      this.date_end_correction = getDate(data['date_end_correction']);
      this.date_end_opinion = getDate(data['date_end_opinion']);
      this.number_corrections = data['number_corrections'] || null;
      this.anonymity = data['anonymity'];
      this.individual_assignment = data['individual_assignment'];
      this.individual_correction = data['individual_correction'];
      this.date_creation = getDate(data['date_creation']);
      this.assignment_instructions = data['assignment_instructions'];
      this.correction_instructions = data['correction_instructions'];
      if (!isUndefined(data['example_assignments'])) {
        data['example_assignments'].forEach(example_assignment => this.example_assignments.push(example_assignment));
      }
      this.active_assignment = data['active_assignment'];
      this.active_correction = data['active_correction'];
      this.assignment_average = data['assignment_average'];
      this.show_assignment_mark = data['show_assignment_mark'];
      this.archived = data['archived'];
      this.mark_round_mode = new MarkMode(data['mark_round_mode']);
      this.mark_precision_mode = new MarkMode(data['mark_precision_mode']);
      this.use_teacher_mark = data['use_teacher_mark'];
      this.mark_mode = new MarkMode(data['mark_mode']);
      this.teacher = new User(data['teacher']);
      this.lesson = new Lesson(data['lesson']);
      if (!isUndefined(data['users'])) {
        data['users'].forEach(user => this.users.push(new User(user)));
      }
      if (!isUndefined(data['groups'])) {
        data['groups'].forEach(group => this.groups.push(new Group(group)));
      }
      if (!isUndefined(data['assignments'])) {
        data['assignments'].forEach(assignment =>
          this.assignments.push(new Assignment(assignment, teacher)));
      }
      if (!isUndefined(data['sections'])) {
        data['sections'].forEach(section =>
          this.sections.push(new Section(section)));
      }
    }
  }

  getLessonId() {
    return this.lesson !== null ? [this.lesson.id] : null;
  }
  getUsersId() {
    const ids = [];
    for (const user of this.users) {
      ids.push(user.id);
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
  getSectionsId() {
    const ids = [];
    for (const section of this.sections) {
      ids.push(section.id);
    }
    return ids;
  }
  getCriteriasId() {
    const ids = [];
    let c = 0;
    for (const section of this.sections) {
      ids.push([]);
      for (const criteria of section.criterias) {
        ids[c].push(criteria.id);
      }
      c++;
    }
    return ids;
  }

}

export class Assignment {
  id: number = null;
  draft: boolean = true;
  date_submission: Date = null;
  raw_mark: number = null;
  standard_deviation: number = null;
  reliability: number = null;
  weighted_mark: number = null;
  mark: number = null;
  file: string = null;
  date_creation: Date = null;
  user: User = null;
  group: Group = null;
  evaluation: Evaluation = null;
  correction: Correction = null;
  corrections: Correction[] = [];
  assignment_sections: AssignmentSection[] = [];
  warning: boolean = false;

  constructor(data?: any, teacher?: boolean) {
    if (!isUndefined(data)) {
      this.id = data['id'];
      this.draft = data['draft'];
      this.date_submission = getDate(data['date_submission']);
      this.raw_mark = data['raw_mark'];
      this.standard_deviation = data['standard_deviation'];
      this.reliability = data['reliability'];
      this.weighted_mark = data['weighted_mark'];
      this.mark = data['mark'];
      this.file = data['file'];
      this.date_creation = getDate(data['date_creation']);
      this.user = new User(data['user']);
      this.group = new Group(data['group']);
      this.evaluation = new Evaluation(data['evaluation']);
      this.correction = new Correction(data['correction']);
      if (!isUndefined(data['corrections'])) {
        data['corrections'].forEach(correction => {
          if ((isUndefined(teacher) || !teacher) && !isUndefined(correction.user)) {
            correction.user.role.id !== 3 ? this.corrections.push(new Correction(correction)) : null;
          } else {
            this.corrections.push(new Correction(correction));
          }
        });
      }
      if (!isUndefined(data['assignment_sections'])) {
        data['assignment_sections'].forEach(assignment_section =>
          this.assignment_sections.push(new AssignmentSection(assignment_section)));
      }
      this.warning = data['warning'];
    }
  }

  isFinished(): boolean {
    if (this.evaluation != null) {
      return (this.evaluation.date_end_assignment < new Date());
    }
    return false;
  }

  isOpinionFinished(): boolean {
    if (this.evaluation != null) {
      return (this.evaluation.date_end_opinion < new Date());
    }
    return false;
  }
}

export class AssignmentSection {
  id: number = null;
  answer: string = null;
  assignment: Assignment = null;
  section: Section = null;
  correction_sections: CorrectionSection[] = [];
  assignment_criterias: AssignmentCriteria[] = [];

  constructor(data?: any) {
    if (!isUndefined(data)) {
      this.id = data['id'];
      this.answer = data['answer'];
      this.assignment = new Assignment(data['assignment']);
      this.section = new Section(data['section']);
      if (!isUndefined(data['assignment_criterias'])) {
        data['assignment_criterias'].forEach(assignment_criteria =>
          this.assignment_criterias.push(new AssignmentCriteria(assignment_criteria)));
      }
    }
  }

}

export class AssignmentCriteria {
  id: number = null;
  raw_mark: number = null;
  standard_deviation: number = null;
  reliability: number = null;
  weighted_mark: number = null;
  mark: number = null;
  criteria: Criteria = null;
  assignment_section: AssignmentSection = null;

  constructor(data?: any) {
    if (!isUndefined(data)) {
      this.id = data['id'];
      this.raw_mark = data['raw_mark'];
      this.standard_deviation = data['standard_deviation'];
      this.reliability = data['reliability'];
      this.weighted_mark = data['weighted_mark'];
      this.mark = data['mark'];
      this.criteria = new Criteria(data['criteria']);
      this.assignment_section = new AssignmentSection(data['assignment_section']);
    }
  }

}

export class Section {
  id: number = null;
  title: string = null;
  subject: string = null;
  order: number = null;
  show_mark: boolean = null;
  weight: number = null;
  max_size: number = null;
  limit_file_types: boolean = false;
  section_type: SectionType = new SectionType();
  evaluation: Evaluation = null;
  criterias: Criteria[] = [];
  assignment_sections: AssignmentSection[] = [];
  file_types: FileType[] = [];

  constructor(data?: any) {
    if (!isUndefined(data)) {
      this.id = data['id'];
      this.title = data['title'];
      this.subject = data['subject'];
      this.order = data['order'];
      this.show_mark = data['show_mark'];
      this.weight = data['weight'];
      this.max_size = data['max_size'];
      this.limit_file_types = data['limit_file_types'];
      this.section_type = new SectionType(data['section_type']);
      this.evaluation = new Evaluation(data['evaluation']);
      if (!isUndefined(data['criterias'])) {
        data['criterias'].forEach(criteria =>
          this.criterias.push(new Criteria(criteria)));
      }
      if (!isUndefined(data['assignment_sections'])) {
        data['assignment_sections'].forEach(assignment_section =>
          this.assignment_sections.push(new AssignmentSection(assignment_section)));
      }
      if (!isUndefined(data['file_types'])) {
        data['file_types'].forEach(file_type =>
          this.file_types.push(new FileType(file_type)));
      }
    }
  }

  getSectionTypeId(): number[] {
    return this.section_type.id !== null ? [this.section_type.id] : [];
  }

  getSectionFileTypesIds(): number[] {
    let ids: number[];

    ids = [];
    this.file_types.forEach(fileType => ids.push(fileType.id));
    return ids;
  }

}

export class SectionType {
  id: number = null;
  label: string = null;
  options: string[] = [];

  constructor(data?: any) {
    if (!isUndefined(data)) {
      this.id = data['id'];
      this.label = data['label'];
      this.options = data['options'];
    }
  }

}

export class Correction {
  id: number = null;
  draft: boolean = true;
  date_submission: Date = null;
  reliability: number = null;
  recalculated_reliability: number = null;
  mark: number = null;
  date_creation: Date = null;
  user: User = null;
  group: Group = null;
  assignment: Assignment = null;
  correction_sections: CorrectionSection[] = [];
  thumbs_up: number = 0;
  thumbs_down: number = 0;

  constructor(data?: any) {
    if (!isUndefined(data)) {
      this.id = data['id'];
      this.draft = data['draft'];
      this.date_submission = getDate(data['date_submission']);
      this.reliability = data['reliability'];
      this.recalculated_reliability = data['recalculated_reliability'];
      this.mark = data['mark'];
      this.date_creation = getDate(data['date_creation']);
      this.user = new User(data['user']);
      this.group = new Group(data['group']);
      this.assignment = new Assignment(data['assignment']);
      if (!isUndefined(data['correction_sections'])) {
        data['correction_sections'].forEach(correction_section =>
          this.correction_sections.push(new CorrectionSection(correction_section)));
      }
      this.thumbs_up = data['thumbs_up'];
      this.thumbs_down = data['thumbs_down'];
    }
  }

  isFinished(): boolean {
    if (this.assignment.evaluation != null) {
      return (this.assignment.evaluation.date_end_correction < new Date());
    }
    return false;
  }

}

export class CorrectionSection {
  id: number = null;
  correction: Correction = null;
  assignment_section: AssignmentSection = null;
  correction_criterias: CorrectionCriteria[] = [];

  constructor(data?: any) {
    if (!isUndefined(data)) {
      this.id = data['id'];
      this.correction = new Correction(data['correction']);
      this.assignment_section = new AssignmentSection(data['assignment_section']);
      if (!isUndefined(data['correction_criterias'])) {
        data['correction_criterias'].forEach(correction_criteria =>
          this.correction_criterias.push(new CorrectionCriteria(correction_criteria)));
      }
    }
  }

}

export class CorrectionCriteria {
  id: number = null;
  reliability: number = null;
  recalculated_reliability: number = null;
  mark: number = null;
  comments: string = null;
  criteria: Criteria = null;
  correction_section: CorrectionSection = null;
  correction: Correction = null;
  correction_opinion: Opinion = null;

  constructor(data?: any) {
    if (!isUndefined(data)) {
      this.id = data['id'];
      this.reliability = data['reliability'];
      this.recalculated_reliability = data['recalculated_reliability'];
      this.mark = data['mark'];
      this.comments = data['comments'];
      this.criteria = new Criteria(data['criteria']);
      this.correction_section = new CorrectionSection(data['correction_section']);
      this.correction = new Correction(data['correction']);
      this.correction_opinion = new Opinion(data['correction_opinion']);
    }
  }

}

export class Opinion {
  id: number = null;
  opinion: number;
  comments: string;

  constructor(data?: any) {
    if (!isUndefined(data)) {
      this.id = data['id'];
      this.opinion = data['opinion'];
      this.comments = data['comments'];
    }
  }

}

export class Criteria {
  id: number = null;
  description: string = null;
  order: number = null;
  precision: number = null;
  mark_min: number = 0;
  mark_max: number = null;
  show_mark: boolean = null;
  show_teacher_comments: boolean = null;
  show_student_comments: boolean = null;
  criteria_choices: CriteriaChoice[] = [];
  criteria_type: CriteriaType = null;
  section: Section = null;
  trapezium: Trapezium = null;
  assignment_criteria: AssignmentCriteria = null;
  correction_criterias: CorrectionCriteria[] = [];
  weight: number = 1;
  chart: { label: number } = null;

  constructor(data?: any) {
    if (!isUndefined(data)) {
      this.id = data['id'];
      this.description = data['description'];
      this.order = data['order'];
      this.precision = data['precision'];
      this.mark_min = data['mark_min'];
      this.mark_max = data['mark_max'];
      this.show_mark = data['show_mark'];
      this.show_teacher_comments = data['show_teacher_comments'];
      this.show_student_comments = data['show_student_comments'];
      if (!isUndefined(data['criteria_choices'])) {
        data['criteria_choices'].forEach(criteria_choice =>
          this.criteria_choices.push(new CriteriaChoice(criteria_choice)));
      }
      this.criteria_type = new CriteriaType(data['criteria_type']);
      this.section = new Section(data['section']);
      this.trapezium = new Trapezium(data['trapezium']);
      this.assignment_criteria = new AssignmentCriteria(data['assignment_criteria']);
      if (!isUndefined(data['correction_criterias'])) {
        data['correction_criterias'].forEach(correction_criteria =>
          this.correction_criterias.push(new CorrectionCriteria(correction_criteria)));
      }
      this.weight = data['weight'];
      this.chart = data['chart'];
    }
  }

  getChoicesId() {
    const ids = [];
    if (!isNullOrUndefined(this.criteria_choices)) {
      for (const choice of this.criteria_choices) {
        ids.push(choice.id);
      }
    }
    return ids;
  }

}

export class CriteriaChoice {
  id: number = null;
  criteria_id: number = null;
  mark: number = null;
  name: string = null;

  constructor(data?: any) {
    if (!isUndefined(data)) {
      this.id = data['id'];
      this.criteria_id = data['criteria_id'];
      this.mark = data['mark'];
      this.name = data['name'];
    }
  }

}

export class CriteriaType {
  id: number = null;
  type: string = null;

  constructor(data?: any) {
    if (!isUndefined(data)) {
      this.id = data['id'];
      this.type = data['type'];
    }
  }

}

export class SummaryAssets {
  icon: string = null;
  action: string = null;
  image: string = null;
  author: string = null;
  finished: boolean = null;

  constructor(data?: any) {
    if (!isUndefined(data)) {
      this.icon = data['icon'];
      this.action = data['action'];
      this.image = data['image'];
      this.author = data['author'];
      this.finished = data['finished'];
    }
  }
}

export class Trapezium {
  id: number;
  min0: number;
  max0: number;
  min100: number;
  max100: number;
  criteria: Criteria;

  constructor(data?: any) {
    if (!isUndefined(data)) {
      this.id = data['id'];
      this.min0 = parseFloat(data['min0']);
      this.max0 = parseFloat(data['max0']);
      this.min100 = parseFloat(data['min100']);
      this.max100 = parseFloat(data['max100']);
      this.criteria = new Criteria(data['criteria']);
    }
  }

}

export class MarkMode {
  id: number;
  mode: string;
  description: string;

  constructor(data?: any) {
    if (!isUndefined(data)) {
      this.id = data['id'];
      this.mode = data['mode'];
      this.description = data['description'];
    }
  }

}

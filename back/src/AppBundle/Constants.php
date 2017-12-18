<?php

namespace AppBundle;

class Constants
{

    // Paths formats
    const USER_FILE_PATH_FORMAT = 'users/%d/';
    const LESSON_FILE_PATH_FORMAT = 'lessons/%d/';
    const EVALUATION_EXAMPLE_ASSIGNMENT_FILE_PATH_FORMAT = 'evaluations/%d/example_assignments/';
    const EVALUATION_SUBJECT_FILE_PATH_FORMAT = 'evaluations/%d/subject_files/';
    const ASSIGNMENT_FILE_PATH_FORMAT = 'assignments/%d';
    const ASSIGNMENT_SECTION_FILE_PATH_FORMAT = 'assignments/%d/sections/%d/';

    // Values constants
    const SECTION_TYPE_FILE = 2;
    const ROLE_ADMIN = 1;
    const ROLE_STUDENT = 2;
    const ROLE_TEACHER = 3;
    const UPLOAD_MAX_SIZE = 1;

    // Settings value types
    const SETTING_INTEGER_TYPE = 'INTEGER';

    // CRITERIAS
    const CRITERIA_TYPE_COMMENT = 1;
    const CRITERIA_TYPE_CHOICE = 2;
    const CRITERIA_TYPE_JUDGMENT = 3;

    //EVALUATION PROPERTIES
    const EVALUATION_MARK_MODE_AVERAGE = 1;
    const EVALUATION_MARK_MODE_WEIGHTED_AVERAGE = 2;
    const EVALUATION_MARK_MODE_DEFAULT = self::EVALUATION_MARK_MODE_WEIGHTED_AVERAGE;

    const EVALUATION_MARK_PRECISION_MODE_POINT = 1;
    const EVALUATION_MARK_PRECISION_MODE_HALF_POINT = 2;
    const EVALUATION_MARK_PRECISION_MODE_ONE_DECIMAL = 3;
    const EVALUATION_MARK_PRECISION_MODE_TWO_DECIMAL = 4;
    const EVALUATION_MARK_PRECISION_MODE_DEFAULT = self::EVALUATION_MARK_PRECISION_MODE_POINT;

    const EVALUATION_MARK_ROUND_MODE_NEAR = 1;
    const EVALUATION_MARK_ROUND_MODE_ABOVE = 2;
    const EVALUATION_MARK_ROUND_MODE_BELOW = 3;
    const EVALUATION_MARK_ROUND_MODE_DEFAULT = self::EVALUATION_MARK_ROUND_MODE_NEAR;

    const DATE_FORMAT = 'd/m/Y à H:i:s';

    const VARS = [
        'date_start_assignment' => 'à la date de début du devoir',
        'date_end_assignment' => 'à la date de fin du devoir',
        'date_start_correction' => 'à la date de début de la correction',
        'date_end_correction' => 'à la date de fin de la correction',
        'date_end_opinion' => 'à la date de fin de l\'opinion',
        'mark_min' => 'à la note minimale',
        'mark_max' => 'à la note maximale',
        'precision' => 'la précision',
        'num_users' => 'au nombre d\'étudiants',
        'num_groups' => 'au nombre de groupes',
        'min0' => 'à min0',
        'min100' => 'à min100',
        'max100' => 'à max100',
        'groups' => 'groupes',
        'users' => 'étudiants',
        'individual_assignment' => 'individuel',
        'group_assignment' => 'de groupe',
        'submitted_assignment' => 'le devoir rendu',
        'submitted_correction' => 'la correction rendue'
    ];
}

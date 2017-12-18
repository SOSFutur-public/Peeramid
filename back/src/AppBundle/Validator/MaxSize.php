<?php

namespace AppBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Created by PhpStorm.
 * User: Nicolas
 * Date: 21/09/2017
 * Time: 09:20
 */

/**
 * Class SectionFile
 * @package AppBundle\Validator
 *
 * @Annotation
 */
class MaxSize extends Constraint
{
    public function validatedBy()
    {
        return 'max_size_validator';
    }
}
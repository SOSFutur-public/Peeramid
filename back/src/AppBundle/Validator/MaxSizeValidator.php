<?php
/**
 * Created by PhpStorm.
 * User: Nicolas
 * Date: 21/09/2017
 * Time: 09:24
 */

namespace AppBundle\Validator;


use AppBundle\Constants;
use AppBundle\Entity\Setting;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class MaxSizeValidator extends ConstraintValidator
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Checks if the passed value is valid.
     *
     * @param mixed $value The value that should be validated
     * @param Constraint $constraint The constraint for the validation
     */
    public function validate($value, Constraint $constraint)
    {
        /** @var Setting $setting */
        $setting = $this->em->getRepository('AppBundle:Setting')->find(Constants::UPLOAD_MAX_SIZE);
        $adminMaxSize = $setting->getValue();
        if ($value != null) {
            if ($value > $adminMaxSize) {
                $this->context->addViolation('La taille maximum ne peut pas être supérieure à ' . $adminMaxSize . ' Mo.');
            }
        }
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: Nicolas
 * Date: 12/10/2017
 * Time: 11:55
 */

namespace AppBundle\Service;


use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;

class ValidatorService
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param $object
     * @return array
     */
    public function validate($object)
    {
        $validator = $this->container->get('validator');
        $listErrors = $validator->validate($object);
        $errors = array();
        if (count($listErrors) > 0) {
            foreach ($listErrors as $error) {
                /** @var ConstraintViolationInterface $error */
                $errors[] = array(
                    'field' => $error->getPropertyPath(),
                    'value' => $error->getInvalidValue(),
                    'message' => $error->getMessage()
                );
            }
        }
        return $errors;
    }
}
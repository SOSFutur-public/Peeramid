<?php
/**
 * Created by PhpStorm.
 * User: Nicolas
 * Date: 11/10/2017
 * Time: 15:10
 */

namespace AppBundle\Validator;


use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class CollectionSameItemValidator extends ConstraintValidator
{

    public function validate($value, Constraint $constraint)
    {
        if (null === $value) {
            return;
        }

        $accessor = new PropertyAccessor();
        $value = $accessor->getValue($value, $constraint->collection);

        if (!is_array($value) && !$value instanceof \Countable) {
            throw new UnexpectedTypeException($value, 'array or \Countable');
        }

        if (count($value)) {
            $repeatedItems = [];
            $existedItems = [];

            $itemIndex = 0;
            foreach ($value as $item) {
                if ($constraint->variable) {
                    $variable = $accessor->getValue($item, $constraint->variable);
                } else {
                    $variable = $item;
                }
                if (in_array($variable, $existedItems)) {
                    if (!in_array($variable, $repeatedItems)) {
                        $repeatedItems[] = [
                            'itemIndex' => $itemIndex,
                            'item' => $item
                        ];
                    }
                } else {
                    $existedItems[] = $variable;
                }
                $itemIndex++;
            }

            foreach ($repeatedItems as $item) {
                $value = null;
                if ($constraint->toString) {
                    $value = $accessor->getValue($item['item'], $constraint->toString);
                }

                foreach (array_unique(array_map('trim', explode(',', $constraint->errorPath))) as $path) {
                    $this->addViolation(trim($path), $constraint, $item, $value);
                }
            }
        }
    }

    private function addViolation($path, Constraint $constraint, $row, $value = null)
    {
        $atPath = null;

        if ($path == CollectionSameItem::ERROR_PATH_FIELD && $constraint->errorForm) {
            $atPath = sprintf('%s[%d].%s', $constraint->collection, $row['itemIndex'], $constraint->variable);
        }

        $violation = $this->context
            ->buildViolation('already_used')
            ->setParameter('%collectionName%', $constraint->collection)
            ->atPath($atPath)
            ->setInvalidValue($value);

        if ($value) {
            $violation->setParameter('%variable%', $value);
        }

        $violation->addViolation();
    }
}
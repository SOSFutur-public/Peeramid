<?php
/**
 * Created by PhpStorm.
 * User: Nicolas
 * Date: 11/10/2017
 * Time: 09:27
 */

namespace AppBundle\Form\EventSubscriber;


use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class SortCorrectionSubscriber implements EventSubscriberInterface
{

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(FormEvents::PRE_SUBMIT => 'preSubmit');
    }

    public function preSubmit(FormEvent $event)
    {
        $data = $event->getData();

        if (null === $data) {
            $data = array();
        }

        if (!is_array($data) && !$data instanceof \Traversable) {
            throw new UnexpectedTypeException($data, 'array or \Traversable');
        }

        if (array_key_exists('correction_sections', $data)) {
            $correctionSections = $data['correction_sections'];

            if ($correctionSections !== null) {
                if (!is_array($correctionSections) && !$correctionSections instanceof \Traversable) {
                    throw new UnexpectedTypeException($correctionSections, 'array or \Traversable');
                }

                foreach ($correctionSections as $i => $correctionSection) {
                    if (array_key_exists('correction_criterias', $correctionSection)) {
                        $correctionCriterias = $correctionSection['correction_criterias'];

                        if ($correctionCriterias !== null) {
                            if (!is_array($correctionCriterias) && !$correctionCriterias instanceof \Traversable) {
                                throw new UnexpectedTypeException($correctionCriterias, 'array or \Traversable');
                            }

                            $correctionCriterias = $this->sortById($correctionCriterias);

                            $correctionSection['correction_criterias'] = array_values($correctionCriterias);
                        }
                        $correctionSections[$i] = $correctionSection;
                    }
                }

                $correctionSections = $this->sortById($correctionSections);

                $data['correction_sections'] = array_values($correctionSections);
            }

            $event->setData($data);
        }
    }

    private function sortById($array)
    {
        usort($array, function ($a, $b) {
            if (isset($a['id'])) {
                if (isset($b['id'])) {
                    return ($a['id'] < $b['id']) ? -1 : 1;
                }
            }
            return 0;
        });

        return $array;
    }
}
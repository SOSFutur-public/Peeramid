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

class SortAssignmentSubscriber implements EventSubscriberInterface
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

        if (array_key_exists('assignment_sections', $data)) {
            $assignmentSections = $data['assignment_sections'];

            if ($assignmentSections !== null) {
                if (!is_array($assignmentSections) && !$assignmentSections instanceof \Traversable) {
                    throw new UnexpectedTypeException($assignmentSections, 'array or \Traversable');
                }

                $assignmentSections = $this->sortById($assignmentSections);

                $data['assignment_sections'] = array_values($assignmentSections);
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
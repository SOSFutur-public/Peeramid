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

class SortSectionsSubscriber implements EventSubscriberInterface
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

        if (array_key_exists('sections', $data)) {
            $sections = $data['sections'];

            if ($sections !== null) {
                if (!is_array($sections) && !$sections instanceof \Traversable) {
                    throw new UnexpectedTypeException($sections, 'array or \Traversable');
                }

                foreach ($sections as $i => $section) {
                    if (array_key_exists('criterias', $section)) {
                        $criterias = $section['criterias'];

                        if ($criterias !== null) {
                            if (!is_array($criterias) && !$criterias instanceof \Traversable) {
                                throw new UnexpectedTypeException($criterias, 'array or \Traversable');
                            }

                            $criterias = $this->sortByIdAndOrder($criterias);

                            $section['criterias'] = array_values($criterias);
                        }
                        $sections[$i] = $section;
                    }
                }

                $sections = $this->sortByIdAndOrder($sections);

                $data['sections'] = array_values($sections);
            }

            $event->setData($data);
        }
    }

    private function sortByIdAndOrder($array)
    {
        usort($array, function ($a, $b) {
            if (isset($a['id'])) {
                if (isset($b['id'])) {
                    return ($a['id'] < $b['id']) ? -1 : 1;
                } else {
                    return -1;
                }
            } else {
                if (isset($b['id'])) {
                    return 1;
                } else {
                    if (isset($a['order']) && isset($b['order'])) {
                        return ($a['order'] < $b['order']) ? -1 : 1;
                    }
                }
            }
            return 0;
        });

        return $array;
    }
}
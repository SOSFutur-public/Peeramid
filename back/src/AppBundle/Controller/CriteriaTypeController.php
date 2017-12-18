<?php

namespace AppBundle\Controller;

use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CriteriaTypeController
 * @package AppBundle\Controller
 * @RouteResource("criteriaType")
 *
 */
class CriteriaTypeController extends FOSRestController
{
    /**
     * Get all CriteriaTypes
     */
    public function cgetAction()
    {
        $category = $this->getDoctrine()
            ->getRepository('AppBundle:CriteriaType')
            ->findBy(array(), array('id' => 'asc'));
        $temp = $this->get('serializer')->serialize($category, 'json');

        return new Response($temp);
    }
}
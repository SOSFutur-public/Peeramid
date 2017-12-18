<?php

namespace AppBundle\Controller;

use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CategoryController
 * @package AppBundle\Controller
 * @RouteResource("category")
 */
class CategoryController extends FOSRestController
{
    /**
     * Get all Categories
     *
     * @return Response
     */
    public function cgetAction()
    {
        $categories = $this->getDoctrine()
            ->getRepository('AppBundle:Category')
            ->findBy(array(), array('name' => 'asc'));

        return new Response($this->get('serializer')->serialize($categories, 'json'));
    }

}
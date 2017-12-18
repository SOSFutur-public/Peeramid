<?php

namespace AppBundle\Controller;

use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\FOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class SectionTypeController
 * @package AppBundle\Controller
 * @RouteResource("SectionType")
 */
class SectionTypeController extends FOSRestController
{
    /** Get all section types
     * @Security("is_granted('ROLE_TEACHER')")
     * @return Response
     */
    public function cgetAction()
    {
        $sectionType = $this->getDoctrine()
            ->getRepository('AppBundle:SectionType')
            ->findBy(array(), array('id' => "ASC"));

        $temp = $this->get('serializer')->serialize($sectionType, 'json');
        return new Response($temp);
    }
}
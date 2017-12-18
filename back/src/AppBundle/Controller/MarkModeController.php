<?php
/**
 * Created by PhpStorm.
 * User: Nicolas
 * Date: 28/09/2017
 * Time: 10:21
 */

namespace AppBundle\Controller;


use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\FOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class MarkModeController
 * @package AppBundle\Controller
 * @RouteResource("MarkMode")
 */
class MarkModeController extends FOSRestController
{
    /**
     * Get all MarkModes
     * @Security("is_granted('ROLE_TEACHER')")
     * @return Response
     */
    public function cgetAction()
    {
        $markModes = $this->getDoctrine()
            ->getRepository('AppBundle:MarkMode')
            ->findAll();

        return new Response($this->get('serializer')->serialize($markModes, 'json'));
    }
}
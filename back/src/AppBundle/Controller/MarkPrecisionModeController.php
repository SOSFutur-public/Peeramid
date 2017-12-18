<?php
/**
 * Created by PhpStorm.
 * User: Nicolas
 * Date: 28/09/2017
 * Time: 10:27
 */

namespace AppBundle\Controller;


use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\FOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class MarkPrecisionModeController
 * @package AppBundle\Controller
 * @RouteResource("MarkPrecisionMode")
 */
class MarkPrecisionModeController extends FOSRestController
{
    /**
     * Get all MarkPrecisionModes
     * @Security("is_granted('ROLE_TEACHER')")
     * @return Response
     */
    public function cgetAction()
    {
        $markPrecisionModes = $this->getDoctrine()
            ->getRepository('AppBundle:MarkPrecisionMode')
            ->findAll();

        return new Response($this->get('serializer')->serialize($markPrecisionModes, 'json'));
    }
}
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
 * Class MarkRoundModeController
 * @package AppBundle\Controller
 * @RouteResource("MarkRoundMode")
 */
class MarkRoundModeController extends FOSRestController
{
    /**
     * Get all MarkRoundModes
     * @Security("is_granted('ROLE_TEACHER')")
     * @return Response
     */
    public function cgetAction()
    {
        $markRoundModes = $this->getDoctrine()
            ->getRepository('AppBundle:MarkRoundMode')
            ->findAll();

        return new Response($this->get('serializer')->serialize($markRoundModes, 'json'));
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: Nicolas
 * Date: 01/12/2017
 * Time: 09:55
 */

namespace AppBundle\Controller;


use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ErrorController
 * @package AppBundle\Controller
 * @RouteResource("Error")
 */
class ErrorController extends FOSRestController
{
    /**
     * @param Request $request
     * @return Response
     */
    public function postAction(Request $request)
    {
        $params = $request->request->all();

        $error = $params['error'];

        $this->get('app.service.mail_service')->sendErrorMail($this->getUser(), $error, $request->getClientIp());

        return new JsonResponse();
    }
}
<?php

namespace AppBundle\Controller;


use AppBundle\Entity\User;
use AppBundle\Repository\UserRepository;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\FOSRestController;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class LoginController
 * @package AppBundle\Controller
 * @RouteResource("Login", pluralize=false)
 */
class LoginController extends FOSRestController
{
    /**
     * @param Request $request
     * @return Response
     */
    public function postAction(Request $request)
    {
        // Get request params
        $params = $request->request->all();

        if (!isset($params['username'])) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => array(
                    'message' => 'Veuillez entrer un nom d\'utilisateur.'
                )
            )));
        }
        if (!isset($params['password'])) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => array(
                    'message' => 'Veuillez entrer un mot de passe.'
                )
            )));
        }

        /** @var UserRepository $userRepository */
        $userRepository = $this->getDoctrine()->getRepository('AppBundle:User');
        $users = $userRepository->findByUsername($params['username']);

        $authenticatedUser = null;
        /** @var User $user */
        foreach ($users as $user) {
            if (password_verify($params['password'], $user->getPassword())) {
                $authenticatedUser = $user;
                break;
            }
        }

        if ($authenticatedUser !== null) {
            $token = $this->get('lexik_jwt_authentication.encoder')
                ->encode([
                    'username' => $user->getUsername(),
                    'role' => $user->getRole()->getId()
                ]);
            $response = [
                "success" => true,
                "data" => $authenticatedUser,
                "token" => $token
            ];


            //ajout d'un log de connection
            $this->container->get('app.services.log_connection')->log($request, $authenticatedUser);

        } else {
            $response = [
                'success' => false,
                'errors' => array(
                    'message' => 'Identifiants invalides.'
                )
            ];
        }

        $json = $this->get('serializer')->serialize($response, 'json', SerializationContext::create()->setGroups(array('id', 'user-light')));

        return new Response($json);
    }
}
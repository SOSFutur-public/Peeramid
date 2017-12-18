<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Group;
use AppBundle\Form\GroupType;
use AppBundle\Service\ValidatorService;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\FOSRestController;
use JMS\Serializer\SerializationContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class GroupController
 * @package AppBundle\Controller
 * @RouteResource("group")
 *
 */
class GroupController extends FOSRestController
{
    /**
     * Get all groups
     * @return Response
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function cgetAction()
    {
        // Db query
        $groups = $this->getDoctrine()->getRepository('AppBundle:Group')
            ->findBy(array(), array('name' => 'ASC'));

        // Serialize results
        $json = $this->get('serializer')->serialize(
            $groups,
            'json',
            SerializationContext::create()->setGroups(array('id', 'admin-group-list'))
        );
        return new Response($json);
    }

    /**
     * Gets a group by Id
     *
     * @param integer $id
     * @return Response
     */
    public function getAction($id)
    {
        $group = $this->getDoctrine()->getRepository('AppBundle:Group')->find($id);

        $json = $this->get('serializer')->serialize(
            $group,
            'json',
            SerializationContext::create()->setGroups(array('id', 'admin-group-list'))
        );
        return new Response($json);
    }

    /** Create a group
     * @param Request $request
     * @return Response
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function postAction(Request $request)
    {
        $data = $request->request->all();

        $group = new Group();

        $form = $this->createForm(GroupType::class, $group);
        $form->submit($data);

        // Validate form
        /** @var ValidatorService $validatorService */
        $validatorService = $this->get('app.service.validator');
        $errors = $validatorService->validate($group);
        if (count($errors) > 0) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => $errors
            )));
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($group);
        $em->flush();

        $response = array(
            'success' => true,
            'group' => $group
        );
        $json = $this->get('serializer')->serialize(
            $response,
            'json',
            SerializationContext::create()->setGroups(array('id', 'admin-group-list'))
        );
        return new Response($json);
    }

    /**
     *   Delete a group
     * @param integer $id
     * @Security("is_granted('ROLE_ADMIN')")
     * @return Response
     */
    public function deleteAction($id)
    {
        /** @var Group $group */
        $group = $this->getDoctrine()->getRepository('AppBundle:Group')->find($id);

        if ($group == null) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => array(
                    'message' => 'Ce groupe n\'existe pas.'
                )
            )));
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($group);
        $em->flush();

        return new Response(json_encode(array('success' => true)));
    }

    /** Update general information of a group
     * @param Request $request
     * @Security("is_granted('ROLE_ADMIN')")
     * @return Response
     */
    public function putAction(Request $request)
    {
        $data = $request->request->all();

        /** @var Group $group */
        $group = $this->getDoctrine()->getRepository('AppBundle:Group')->find($request->get('id'));

        if ($group == null) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => array(
                    'message' => 'Ce groupe n\'existe pas.'
                )
            )));
        }

        $form = $this->createForm(GroupType::class, $group);
        $form->submit($data);

        // Validate form
        /** @var ValidatorService $validatorService */
        $validatorService = $this->get('app.service.validator');
        $errors = $validatorService->validate($group);
        if (count($errors) > 0) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => $errors
            )));
        }

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        $response = array(
            'success' => true,
            'group' => $group
        );
        $json = $this->get('serializer')->serialize(
            $response,
            'json',
            SerializationContext::create()->setGroups(array('id', 'admin-group-list'))
        );
        return new Response($json);
    }
}
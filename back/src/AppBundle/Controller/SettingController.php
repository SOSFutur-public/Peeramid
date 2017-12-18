<?php

namespace AppBundle\Controller;

use AppBundle\Constants;
use AppBundle\Entity\Setting;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\FOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class SettingController
 * @package AppBundle\Controller
 * @RouteResource("settings")
 *
 */
class SettingController extends FOSRestController
{
    /**
     * Gets a collection of settings
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function cgetAction()
    {
        $settings = $this->getDoctrine()
            ->getRepository('AppBundle:Setting')
            ->findBy(array(), array('id' => 'asc'));

        $temp = $this->get('serializer')->serialize($settings, 'json');
        return new Response($temp);
    }

    /**
     * @param $id
     * @return Response
     */
    public function getAction($id)
    {
        $setting = $this->getDoctrine()->getRepository('AppBundle:Setting')->find($id);

        $json = $this->get('serializer')->serialize($setting, 'json');
        return new Response($json);
    }

    /**
     * Update setting
     * @param Request $request
     * @Security("is_granted('ROLE_ADMIN')")
     * @return JsonResponse|Response
     */
    public function putAction(Request $request)
    {
        $datas = $request->request->all();

        foreach ($datas as $data) {
            $em = $this->getDoctrine()->getManager();
            /** @var Setting $setting */
            $setting = $em->getRepository('AppBundle:Setting')->find($data['id']);
            if (empty($setting)) {
                return new Response(json_encode(array(
                    'success' => false,
                    'errors' => array(
                        'message' => 'Ce paramètre n\'existe pas.'
                    )
                )));
            }
            switch ($setting->getValueType()) {
                case Constants::SETTING_INTEGER_TYPE:
                    if (!is_int($data['value'])) {
                        return new Response(json_encode(array(
                            'success' => false,
                            'errors' => array(
                                'message' => 'Veuillez rentrer un entier.'
                            )
                        )));
                    }
            }
            $setting->setValue($data['value']);
            $em->flush();
        }
        return new Response(json_encode(array('success' => true)));
    }
}

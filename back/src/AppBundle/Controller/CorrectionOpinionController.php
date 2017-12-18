<?php
/**
 * Created by PhpStorm.
 * User: Nicolas
 * Date: 18/09/2017
 * Time: 17:51
 */

namespace AppBundle\Controller;


use AppBundle\Entity\CorrectionOpinion;
use AppBundle\Form\CorrectionOpinionType;
use AppBundle\Service\CorrectionOpinionService;
use AppBundle\Service\ValidatorService;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\FOSRestController;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CorrectionOpinionController
 * @package AppBundle\Controller
 * @RouteResource("CorrectionOpinion")
 */
class CorrectionOpinionController extends FOSRestController
{
    /**
     * @param Request $request
     * @return Response
     */
    public function putAction(Request $request)
    {
        // Get params
        $data = $request->request->all();

        // Load existing correctionOpinion
        $em = $this->getDoctrine()->getManager();
        /** @var CorrectionOpinion $correctionOpinion */
        $correctionOpinion = $em->getRepository('AppBundle:CorrectionOpinion')->find($data["id"]);

        if ($correctionOpinion === null) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => array(
                    'message' => 'Ce correction_opinion n\'existe pas'
                )
            )));
        }

        $this->get('app.service.access_service')->tryEntity(
            $this->getUser(),
            array(
                $correctionOpinion
                    ->getCorrectionCriteria()
                    ->getCorrectionSection()
                    ->getCorrection()
                    ->getAssignment()
                    ->getUser())
        );

        /** @var CorrectionOpinionService $correctionOpinionService */
        $correctionOpinionService = $this->container->get('app.services.correction_opinion');
        if (!$correctionOpinionService->checkDates($correctionOpinion)) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => array(
                    'message' => 'Vous ne pouvez pas modifier ce correction_opinion.'
                )
            )));
        }

        $form = $this->createForm(CorrectionOpinionType::class, $correctionOpinion);
        $form->submit($data);

        // Validate
        /** @var ValidatorService $validatorService */
        $validatorService = $this->get('app.service.validator');
        $errors = $validatorService->validate($correctionOpinion);
        if (count($errors) > 0) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => $errors
            )));
        }

        $em->flush();

        $response = [
            'success' => true,
            'correction_opinion' => $correctionOpinion
        ];

        $json = $this->get('serializer')->serialize(
            $response,
            'json',
            SerializationContext::create()->setGroups(array('id', 'correction-opinion-edit'))
        );

        return new Response($json);
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: Nicolas
 * Date: 03/10/2017
 * Time: 14:45
 */

namespace AppBundle\Controller;


use AppBundle\Constants;
use AppBundle\Entity\Assignment;
use AppBundle\Entity\Correction;
use AppBundle\Entity\CorrectionCriteria;
use AppBundle\Entity\Criteria;
use AppBundle\Form\CriteriaTrapeziumType;
use AppBundle\Service\StatsService;
use AppBundle\Service\ValidatorService;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\FOSRestController;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class CriteriaController
 * @package AppBundle\Controller
 * @RouteResource("Criteria")
 */
class CriteriaController extends FOSRestController
{
    /**
     * @param $id
     * @return Response
     */
    public function getTrapeziumAction($id)
    {
        /** @var Criteria $criteria */
        $criteria = $this->getDoctrine()->getRepository('AppBundle:Criteria')->find($id);

        if ($criteria == null) {
            throw new NotFoundHttpException('Ce critère n\'existe pas.');
        }

        $this->get('app.service.access_service')->tryEntity(
            $this->getUser(),
            array($criteria->getSection()->getEvaluation()->getTeacher())
        );

        $differences = array();

        /** @var StatsService $statsService */
        $statsService = $this->get('app.services.stats');

        if ($criteria->getCriteriaType()->getId() !== Constants::CRITERIA_TYPE_COMMENT) {
            /** @var CorrectionCriteria $correctionCriteria */
            foreach ($criteria->getCorrectionCriterias() as $correctionCriteria) {
                /** @var Correction $correction */
                $correction = $correctionCriteria->getCorrectionSection()->getCorrection();
                if ($correction->isStudentCorrection()) {
                    if ($correction->getDateSubmission()) {
                        if ($correctionCriteria->getMark() !== null) {
                            $assignmentCriteriaMark = $statsService->getAssignmentCriteriaMark($correctionCriteria);
                            $diff = $correctionCriteria->getMark() - $assignmentCriteriaMark;
                            if (isset($differences[(string)$diff])) {
                                $differences[(string)$diff]++;
                            } else {
                                $differences[(string)$diff] = 1;
                            }
                        }
                    }
                }
            }
            switch ($criteria->getCriteriaType()->getId()) {
                case Constants::CRITERIA_TYPE_CHOICE:
                    $maxChoice = $statsService->getCriteriaChoicesMaxMark($criteria);
                    $minChoice = $statsService->getCriteriaChoicesMinMark($criteria);
                    $criteria->setMaxDiff($maxChoice - $minChoice);
                    break;
                case Constants::CRITERIA_TYPE_JUDGMENT:
                    $criteria->setMaxDiff($criteria->getMarkMax() - $criteria->getMarkMin());
                    break;
            }
        }

        $criteria->setDifferences($differences);

        $json = $this->get('serializer')->serialize(
            $criteria,
            'json',
            SerializationContext::create()->setGroups(array('id', 'trapezium-edit'))
        );
        return new Response($json);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function putTrapeziumAction(Request $request)
    {
        $data = $request->request->all();
        /** @var Criteria $criteria */
        $criteria = $this->getDoctrine()->getRepository('AppBundle:Criteria')->find($data['id']);

        if ($criteria == null) {
            throw new NotFoundHttpException('Ce critère n\'existe pas.');
        }

        $this->get('app.service.access_service')->tryEntity(
            $this->getUser(),
            array($criteria->getSection()->getEvaluation()->getTeacher())
        );

        if ($criteria->getSection()->getEvaluation()->getArchived()) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => array(
                    'message' => 'Vous ne pouvez pas modifier une évaluation archivée.'
                )
            )));
        }

        $form = $this->createForm(CriteriaTrapeziumType::class, $criteria);
        $form->submit($data);

        // Validate form
        /** @var ValidatorService $validatorService */
        $validatorService = $this->get('app.service.validator');
        $errors = $validatorService->validate($criteria);
        if (count($errors) > 0) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => $errors
            )));
        }

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        // Update stats
        /** @var StatsService $statsService */
        $statsService = $this->container->get('app.services.stats');
        /** @var Assignment $assignment */
        foreach ($criteria->getSection()->getEvaluation()->getAssignments() as $assignment) {
            if ($assignment->getDateSubmission()) {
                $statsService->setStats($assignment);
            }
        }

        $response = array(
            'success' => true,
            'criteria' => $criteria
        );

        $json = $this->get('serializer')->serialize(
            $response,
            'json',
            SerializationContext::create()->setGroups(array('id', 'trapezium-edit'))
        );
        return new Response($json);
    }
}
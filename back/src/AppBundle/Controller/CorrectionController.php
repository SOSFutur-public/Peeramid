<?php

namespace AppBundle\Controller;

use AppBundle\Constants;
use AppBundle\Entity\Correction;
use AppBundle\Entity\CorrectionCriteria;
use AppBundle\Entity\CorrectionSection;
use AppBundle\Entity\Criteria;
use AppBundle\Entity\Evaluation;
use AppBundle\Entity\User;
use AppBundle\Form\CorrectionType;
use AppBundle\Repository\CorrectionRepository;
use AppBundle\Service\CorrectionService;
use AppBundle\Service\StatsService;
use AppBundle\Service\ValidatorService;
use DateTime;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\FOSRestController;
use JMS\Serializer\SerializationContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class CorrectionController
 * @package AppBundle\Controller
 * @RouteResource("Correction")
 *
 */
class CorrectionController extends FOSRestController
{
    /**
     * @param $status
     * @return Response
     * @Security("is_granted('ROLE_STUDENT')")
     * @Get("/corrections/list/{status}")
     */
    public function getCorrectionsListAction($status)
    {
        switch ($status) {
            case 'in-progress':
                $isFinished = false;
                break;
            case 'finished':
                $isFinished = true;
                break;
            default:
                throw new NotFoundHttpException('Cette route n\'existe pas.');
        }

        /** @var User $user */
        $user = $this->getUser();

        /** @var CorrectionRepository $correctionRepository */
        $correctionRepository = $this->getDoctrine()->getRepository('AppBundle:Correction');
        $individualCorrections = $correctionRepository->findCorrectionsByUser(
            $user->getId(),
            $isFinished,
            true
        );
        $groupCorrections = $correctionRepository->findCorrectionsByUser(
            $user->getId(),
            $isFinished,
            false
        );

        /** @var Correction $correction */
        foreach ($individualCorrections as $correction) {
            if ($correction->getAssignment()->getEvaluation()->getAnonymity()) {
                $correction->getAssignment()->setUser(null);
                $correction->getAssignment()->setGroup(null);
            }
        }
        foreach ($groupCorrections as $correction) {
            if ($correction->getAssignment()->getEvaluation()->getAnonymity()) {
                $correction->getAssignment()->setUser(null);
                $correction->getAssignment()->setGroup(null);
            }
        }

        $response = array(
            'individual_corrections' => $individualCorrections,
            'group_corrections' => $groupCorrections
        );
        $json = $this->get('serializer')->serialize(
            $response,
            'json',
            SerializationContext::create()->setGroups(array('id', 'correction-list'))
        );
        return new Response($json);
    }

    /**
     * @param $lessonId
     * @return Response
     *
     * @Get("/corrections/lessons/{lessonId}")
     */
    public function cgetUserLessonAction($lessonId)
    {
        /** @var User $user */
        $user = $this->getUser();

        /** @var CorrectionRepository $correctionRepository */
        $correctionRepository = $this->getDoctrine()->getRepository('AppBundle:Correction');
        $individualCorrections = $correctionRepository->findByUserAndLesson($user->getId(), $lessonId, true);
        $groupCorrections = $correctionRepository->findByUserAndLesson($user->getId(), $lessonId, false);
        $response = array(
            'individual_corrections' => $individualCorrections,
            'group_corrections' => $groupCorrections
        );
        $json = $this->get('serializer')->serialize(
            $response,
            'json',
            SerializationContext::create()->setGroups(array('id', 'correction-list'))
        );
        return new Response($json);
    }

    /**
     * Get an entity (correction) by its identifier
     * @param integer $id
     * @return Response
     */
    public function getAction($id)
    {
        /** @var Correction $correction */
        $correction = $this->getDoctrine()->getRepository('AppBundle:Correction')->find($id);

        $serializationGroups = array('id', 'correction-edit');

        if ($correction === null) {
            throw new NotFoundHttpException("Cette correction n'existe pas");
        }

        if (!$correction->getAssignment()->getEvaluation()->getAnonymity()) {
            $serializationGroups[] = 'not-anonymous-correction';
        }

        /** @var User $currentUser */
        $currentUser = $this->getUser();
        if ($currentUser->getRole()->getId() === Constants::ROLE_TEACHER) {
            $serializationGroups[] = 'teacher-correction';
        }

        $this->get('app.service.access_service')->tryEntity(
            $this->getUser(),
            array($correction->getUser(), $correction->getAssignment()->getEvaluation()->getTeacher())
        );

        // Response serialize
        $json = $this->get('serializer')->serialize(
            $correction,
            'json',
            SerializationContext::create()->setGroups($serializationGroups)
        );

        return new Response($json);
    }

    /**
     * Update an existing correction
     *
     * @param Request $request
     * @return Response
     */
    public function putAction(Request $request)
    {
        /** @var CorrectionRepository $correctionRepository */
        $correctionRepository = $this->getDoctrine()->getRepository('AppBundle:Correction');
        /** @var Correction $correction */
        $correction = $correctionRepository->find($request->get('id'));
        if ($correction === null) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => array(
                    'message' => 'Cette correction n\'existe pas.'
                )
            )));
        }

        $this->get('app.service.access_service')->tryEntity(
            $this->getUser(),
            array($correction->getUser())
        );

        // Check correction status before updating it (security/integrity reasons)
        /** @var CorrectionService $correctionService */
        $correctionService = $this->get('app.services.correction');
        if (!$correctionService->checkCorrectionContext($correction)) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => array(
                    'message' => 'Vous ne pouvez pas modifier cette correction.'
                )
            )));
        }

        // Create form from posted data
        $form = $this->createForm(CorrectionType::class, $correction);

        $data = $request->request->all();
        $form->submit($data);

        // Validate form
        /** @var ValidatorService $validatorService */
        $validatorService = $this->get('app.service.validator');
        $errors = $validatorService->validate($correction);
        if (count($errors) > 0) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => $errors
            )));
        }

        if (!$correction->getDraft()) {
            $correction->setDateSubmission(new DateTime());
            // Update stats
            /** @var StatsService $statsService */
            $statsService = $this->container->get('app.services.stats');
            $statsService->setStats($correction->getAssignment());
        }

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        if (!$correction->getDraft()) {
            try {
                $this->sendConfirmationMail($correction);
            } catch (\Exception $exception) {

            }
        }

        $response = array(
            'success' => true,
            'correction' => $correction
        );

        $serializationGroups = array('id', 'correction-edit');
        if (!$correction->getAssignment()->getEvaluation()->getAnonymity()) {
            $serializationGroups[] = 'not-anonymous-correction';
        }

        $json = $this->get('serializer')->serialize(
            $response,
            'json',
            SerializationContext::create()->setGroups($serializationGroups)
        );
        return new Response($json);
    }

    /**
     * @param Correction $correction
     */
    private function sendConfirmationMail(Correction $correction)
    {
        /** @var Evaluation $evaluation */
        $evaluation = $correction->getAssignment()->getEvaluation();
        if ($correction->getUser()) {
            $users = array($correction->getUser());
        } else {
            $users = $correction->getGroup()->getUsers();
        }
        /** @var User $user */
        foreach ($users as $user) {
            $this->get('app.service.mail_service')->send(
                $user,
                'Emails/correction_confirmation.html.twig',
                $evaluation->getName() . ' - Correction rendue',
                array(
                    'name' => $user->getFirstName() . ' ' . $user->getLastName(),
                    'evaluation_name' => $evaluation->getName(),
                    'date' => $evaluation->getDateEndCorrection()->format('d/m/Y H:i:s')
                )
            );
        }
    }

    /**
     * @param $id
     * @return Response
     */
    public function getOpinionsAction($id)
    {
        /** @var Correction $correction */
        $correction = $this->getDoctrine()->getRepository('AppBundle:Correction')->find($id);

        if ($correction === null) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => array(
                    'message' => 'Cette correction n\'existe pas.'
                )
            )));
        }

        $this->get('app.service.access_service')->tryEntity(
            $this->getUser(),
            array($correction->getUser())
        );

        /** @var Evaluation $evaluation */
        $evaluation = $correction->getAssignment()->getEvaluation();

        $now = new DateTime();

        if ($now < $evaluation->getDateEndCorrection()) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => array(
                    'message' => 'Vous ne pouvez pas encore voir les corrections de votre devoir'
                )
            )));
        }

        if (!$evaluation->getShowCorrectionsMark()) {
            $correction->setMark(null);
        }

        /** @var CorrectionSection $correctionSection */
        foreach ($correction->getCorrectionSections() as $correctionSection) {
            /** @var CorrectionCriteria $correctionCriteria */
            foreach ($correctionSection->getCorrectionCriterias() as $correctionCriteria) {
                /** @var Criteria $criteria */
                $criteria = $correctionCriteria->getCriteria();
                if ($correction->getUser()) {
                    switch ($correction->getUser()->getRole()->getId()) {
                        case Constants::ROLE_STUDENT:
                            if (!$criteria->getShowMark() && !$criteria->getShowStudentsComments()) {
                                $correctionSection->removeCorrectionCriteria($correctionCriteria);
                            }
                            break;
                        case Constants::ROLE_TEACHER:
                            if (!$criteria->getShowMark() && !$criteria->getShowTeacherComments()) {
                                $correctionSection->removeCorrectionCriteria($correctionCriteria);
                            }
                            break;
                    }
                } else if ($correction->getGroup()) {
                    if (!$criteria->getShowMark() && !$criteria->getShowStudentsComments()) {
                        $correctionSection->removeCorrectionCriteria($correctionCriteria);
                    }
                }

                if (!$correctionCriteria->getCriteria()->getShowMark()) {
                    $correctionCriteria->setMark(null);
                }
                if ($correction->isStudentCorrection()) {
                    if (!$correctionCriteria->getCriteria()->getShowStudentsComments()) {
                        $correctionCriteria->setComments(null);
                    }
                } else {
                    if (!$correctionCriteria->getCriteria()->getShowTeacherComments()) {
                        $correctionCriteria->setComments(null);
                    }
                }
                // Don't show opinion if date isn't defined
                if (!$evaluation->getDateEndOpinion()) {
                    $correctionCriteria->setCorrectionOpinion(null);
                }
            }
        }

        // Response serialize
        $json = $this->get('serializer')->serialize(
            $correction,
            'json',
            SerializationContext::create()->setGroups(array('id', 'assignment-corrections'))
        );
        return new Response($json);
    }
}
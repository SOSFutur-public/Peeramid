<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Assignment;
use AppBundle\Entity\Correction;
use AppBundle\Entity\User;
use AppBundle\Form\AssignmentType;
use AppBundle\Repository\AssignmentRepository;
use AppBundle\Service\AssignmentService;
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
 * Class AssignmentController
 * @package AppBundle\Controller
 * @RouteResource("Assignment")
 *
 */
class AssignmentController extends FOSRestController
{
    /**
     * @param $status
     * @return Response
     * @Get("/assignments/list/{status}")
     * @Security("is_granted('ROLE_STUDENT')")
     */
    public function cgetAssignmentListAction($status)
    {
        /** @var User $user */
        $user = $this->getUser();

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
        /** @var AssignmentRepository $assignmentRepository */
        $assignmentRepository = $this->getDoctrine()->getRepository('AppBundle:Assignment');
        $individualAssignments = $assignmentRepository->findAssignmentsByUser(
            $user->getId(),
            $isFinished,
            true
        );
        $groupAssignments = $assignmentRepository->findAssignmentsByUser(
            $user->getId(),
            $isFinished,
            false
        );

        $response = array(
            'individual_assignments' => $individualAssignments,
            'group_assignments' => $groupAssignments
        );
        $json = $this->get('serializer')->serialize(
            $response,
            'json',
            SerializationContext::create()->setGroups(array('id', 'assignment-list'))
        );
        return new Response($json);
    }

    /**
     * @param $lessonId
     * @return Response
     *
     * @Get("/assignments/lessons/{lessonId}")
     * @Security("is_granted('ROLE_STUDENT')")
     */
    public function cgetUserLessonAction($lessonId)
    {
        /** @var User $user */
        $user = $this->getUser();

        /** @var AssignmentRepository $assignmentRepository */
        $assignmentRepository = $this->getDoctrine()->getRepository('AppBundle:Assignment');
        $individualAssignments = $assignmentRepository->findByUserAndLesson($user->getId(), $lessonId, true);
        $groupAssignments = $assignmentRepository->findByUserAndLesson($user->getId(), $lessonId, false);
        $response = array(
            'individual_assignments' => $individualAssignments,
            'group_assignments' => $groupAssignments
        );
        $json = $this->get('serializer')->serialize(
            $response,
            'json',
            SerializationContext::create()->setGroups(array('id', 'assignment-list'))
        );
        return new Response($json);
    }

    /**
     * Get a "assignment" by id
     * @param integer $id
     * @return Response
     */
    public function getAction($id)
    {
        /** @var Assignment $assignment */
        $assignment = $this->getDoctrine()->getRepository('AppBundle:Assignment')->find($id);

        /** @var User $user */
        $user = $this->getUser();

        if ($assignment == null) {
            throw new NotFoundHttpException("Ce devoir n'existe pas");
        }

        $this->get('app.service.access_service')->tryEntity(
            $user,
            array($assignment->getUser(), $assignment->getEvaluation()->getTeacher())
        );

        // Response
        $json = $this->get('serializer')->serialize(
            $assignment,
            'json',
            SerializationContext::create()->setGroups(array('id', 'assignment-edit'))
        );
        return new Response($json);
    }

    /**
     * @param $id
     * @return Response
     */
    public function getCorrectionsAction($id)
    {
        /** @var Assignment $assignment */
        $assignment = $this->getDoctrine()->getRepository('AppBundle:Assignment')->find($id);

        if ($assignment == null) {
            throw new NotFoundHttpException("Ce devoir n'existe pas");
        }

        $this->get('app.service.access_service')->tryEntity($this->getUser(), array($assignment->getUser()));

        $now = new DateTime();

        if ($now < $assignment->getEvaluation()->getDateEndCorrection()) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => array(
                    'message' => 'Vous ne pouvez pas encore voir les corrections de votre devoir'
                )
            )));
        }

        if (!$assignment->getEvaluation()->getShowAssignmentMark()) {
            $assignment->setMark(null);
        }

        /** @var Correction $correction */
        foreach ($assignment->getCorrections() as $correction) {
            if ($correction->getDraft()) {
                $assignment->removeCorrection($correction);
            }
            if (!$assignment->getEvaluation()->getShowCorrectionsMark()) {
                $correction->setMark(null);
            }
        }

        // Response
        $json = $this->get('serializer')->serialize(
            $assignment,
            'json',
            SerializationContext::create()->setGroups(array('id', 'assignment-correction-list'))
        );
        return new Response($json);
    }

    /**
     * Update a "assignment"
     * @param Request $request
     * @return Response
     */
    public function putAction(Request $request)
    {
        /** @var AssignmentRepository $assignmentRepository */
        $assignmentRepository = $this->getDoctrine()->getRepository('AppBundle:Assignment');
        /** @var Assignment $assignment */
        $assignment = $assignmentRepository->find($request->get('id'));
        if ($assignment === null) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => array(
                    'message' => 'Ce devoir n\'existe pas.'
                )
            )));
        }

        $this->get('app.service.access_service')->tryEntity(
            $this->getUser(),
            array($assignment->getUser())
        );

        /** @var AssignmentService $assignmentService */
        $assignmentService = $this->container->get('app.services.assignment');
        if (!$assignmentService->checkAssignmentContext($assignment)) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => array(
                    'message' => 'Vous ne pouvez pas modifier ce devoir.'
                )
            )));
        }

        $form = $this->createForm(AssignmentType::class, $assignment);

        $data = $request->request->all();
        $form->submit($data);

        if (!$assignment->isDraft()) {
            $assignment->setDateSubmission(new \DateTime());
            try {
                $this->sendConfirmationMail($assignment);
            } catch (\Exception $exception) {

            }
        }

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        $response = array(
            'success' => true,
            'assignment' => $assignment
        );

        $json = $this->get('serializer')->serialize(
            $response,
            'json',
            SerializationContext::create()->setGroups(array('id', 'assignment-edit'))
        );

        return new Response($json);
    }

    /**
     * @param Assignment $assignment
     */
    private function sendConfirmationMail(Assignment $assignment)
    {
        if ($assignment->getEvaluation()->getIndividualAssignment()) {
            $users = array($assignment->getUser());
        } else {
            $users = $assignment->getGroup()->getUsers();
        }
        /** @var User $user */
        foreach ($users as $user) {
            $this->get('app.service.mail_service')->send(
                $user,
                'Emails/confirmation.html.twig',
                $assignment->getEvaluation()->getName() . ' - Devoir remis',
                array(
                    'name' => $user->getFirstName() . ' ' . $user->getLastName(),
                    'assignment_name' => $assignment->getEvaluation()->getName(),
                    'date' => $assignment->getEvaluation()->getDateEndAssignment()->format('d/m/Y H:i:s')
                )
            );
        }
    }
}
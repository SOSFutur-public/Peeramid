<?php
/**
 * Created by PhpStorm.
 * User: SOSF - Serveur 1
 * Date: 18/10/2017
 * Time: 14:38
 */

namespace AppBundle\Controller;


use AppBundle\Constants;
use AppBundle\Entity\Assignment;
use AppBundle\Entity\AssignmentSection;
use AppBundle\Entity\Correction;
use AppBundle\Entity\Evaluation;
use AppBundle\Entity\Group;
use AppBundle\Entity\Lesson;
use AppBundle\Entity\User;
use AppBundle\Repository\AssignmentSectionRepository;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\MimeType\FileinfoMimeTypeGuesser;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class FileController
 * @package AppBundle\Controller
 * @RouteResource("Files")
 */
class FileController extends FOSRestController
{
    /**
     * @Get("/files/users/{userId}")
     * @param int $userId
     * @return Response
     */
    public function getUsersAction(int $userId)
    {
        /** @var User $user */
        $user = $this->getDoctrine()->getRepository('AppBundle:User')->find($userId);

        if ($user === null) {
            throw new NotFoundHttpException("Cet utilisateur n'existe pas");
        }

        $uploadDirectory = $this->getParameter('upload.directory');

        $filePath = $uploadDirectory .
            sprintf(Constants::USER_FILE_PATH_FORMAT, $user->getId()) . $user->getImage();

        if (!file_exists($filePath))
            return $this->fileDoesntExist($filePath);

        return $this->getFile($user->getImage(), $filePath);
    }

    private function fileDoesntExist($file)
    {
        return new JsonResponse(
            array(
                'success' => false,
                'requested_file' => $file,
                'errors' => array(
                    'message' => 'Ce fichier n\'existe pas'
                )
            )
        );
    }

    private function getFile(string $filename, string $filePath)
    {
        $response = new BinaryFileResponse($filePath);
        $mymeTypeGuesser = new FileinfoMimeTypeGuesser();
        $response->headers->set('Content-Type', $mymeTypeGuesser->guess($filePath));
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $filename
        );

        return $response;
    }

    /**
     * @param int $evaluationId
     * @param string $type
     * @param string $fileName
     * @Get("/files/evaluations/{evaluationId}/{type}/{fileName}")
     * @return Response
     */
    public function getEvaluationsAction(int $evaluationId, string $type, string $fileName)
    {
        $uploadDirectory = $this->getParameter('upload.directory');

        /** @var Evaluation $evaluation */
        $evaluation = $this->getDoctrine()->getRepository('AppBundle:Evaluation')->find($evaluationId);

        if ($evaluation === null) {
            throw new NotFoundHttpException("Cette évaluation n'existe pas");
        }

        switch ($type) {
            case 'example_assignments':
                $filePath = $uploadDirectory .
                    sprintf(Constants::EVALUATION_EXAMPLE_ASSIGNMENT_FILE_PATH_FORMAT, $evaluation->getId()) .
                    $fileName;
                break;
            case 'subject_files':
                $filePath = $uploadDirectory .
                    sprintf(Constants::EVALUATION_SUBJECT_FILE_PATH_FORMAT, $evaluation->getId()) .
                    $fileName;
                break;
            default:
                throw new NotFoundHttpException("Cette route n'existe pas");
        }

        /** @var User $user */
        $user = $this->getUser();

        $allowedUsers = array();
        $allowedUsers[] = $evaluation->getTeacher();
        $now = new \DateTime();
        if ($evaluation->getActiveAssignment() && $now > $evaluation->getDateStartAssignment()) {
            array_push($allowedUsers, $evaluation->getUsers()->toArray());
            /** @var Group $group */
            foreach ($evaluation->getGroups() as $group) {
                array_push($allowedUsers, $group->getUsers()->toArray());
            }
        }

        $this->get('app.service.access_service')->tryEntity($user, $allowedUsers);

        if (!file_exists($filePath)) {
            return $this->fileDoesntExist($filePath);
        }

        return $this->getFile($fileName, $filePath);
    }

    /**
     * @param int $assignmentSectionId
     * @Get("/files/assignmentSections/{assignmentSectionId}")
     * @return BinaryFileResponse|JsonResponse
     */
    public function getAssignmentSectionAction(int $assignmentSectionId)
    {
        $uploadDirectory = $this->getParameter('upload.directory');

        /** @var AssignmentSectionRepository $assignmentSectionRepository */
        $assignmentSectionRepository = $this->getDoctrine()->getRepository('AppBundle:AssignmentSection');
        /** @var AssignmentSection $assignmentSection */
        $assignmentSection = $assignmentSectionRepository->find($assignmentSectionId);

        if ($assignmentSection === null) {
            throw new NotFoundHttpException("Ce devoir n'existe pas");
        }

        /** @var Assignment $assignment */
        $assignment = $assignmentSection->getAssignment();

        $filePath = $uploadDirectory .
            sprintf(
                Constants::ASSIGNMENT_SECTION_FILE_PATH_FORMAT,
                $assignment->getId(),
                $assignmentSection->getId()
            ) .
            $assignmentSection->getAnswer();

        /** @var User $user */
        $user = $this->getUser();

        $allowedUsers = array();
        $allowedUsers[] = $assignment->getEvaluation()->getTeacher();
        $allowedUsers[] = $assignment->getUser();
        if ($assignment->getGroup()) {
            array_push($allowedUsers, $assignment->getGroup()->getUsers()->toArray());
        }

        $now = new \DateTime();

        /** @var Evaluation $evaluation */
        $evaluation = $assignment->getEvaluation();

        if ($evaluation->getActiveCorrection() && $now > $evaluation->getDateStartCorrection()) {
            /** @var Correction $correction */
            foreach ($assignment->getCorrections() as $correction) {
                array_push($allowedUsers, $correction->getUser());
                if ($correction->getGroup()) {
                    array_push($allowedUsers, $correction->getGroup()->getUsers()->toArray());
                }
            }
        }

        $this->get('app.service.access_service')->tryEntity($user, $allowedUsers);

        if (!file_exists($filePath)) {
            return $this->fileDoesntExist($filePath);
        }

        return $this->getFile($assignmentSection->getAnswer(), $filePath);
    }

    /**
     * @Get("/files/lessons/{lessonId}")
     * @param int $lessonId
     * @return Response
     */
    public function getLessonsAction(int $lessonId)
    {
        /** @var Lesson $lesson */
        $lesson = $this->getDoctrine()->getRepository('AppBundle:Lesson')->find($lessonId);

        if ($lesson === null) {
            throw new NotFoundHttpException("Ce cours n'existe pas");
        }

        $uploadDirectory = $this->getParameter('upload.directory');

        $filePath = $uploadDirectory .
            sprintf(Constants::LESSON_FILE_PATH_FORMAT, $lesson->getId()) . $lesson->getImage();

        if (!file_exists($filePath))
            return $this->fileDoesntExist($filePath);

        return $this->getFile($lesson->getImage(), $filePath);
    }

}
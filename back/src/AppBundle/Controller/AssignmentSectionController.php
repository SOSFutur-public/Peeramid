<?php

namespace AppBundle\Controller;

use AppBundle\Constants;
use AppBundle\Entity\AssignmentSection;
use AppBundle\Entity\Section;
use AppBundle\Service\AssignmentService;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\FOSRestController;
use JMS\Serializer\SerializationContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class AssignmentSectionController
 * @package AppBundle\Controller
 * @RouteResource("AssignmentSection")
 */
class AssignmentSectionController extends FOSRestController
{
    /**
     * @param integer $id
     * @param Request $request
     * @return Response
     * @Security("is_granted('ROLE_STUDENT')")
     */
    public function postFileAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var AssignmentSection $assignmentSection */
        $assignmentSection = $em->getRepository('AppBundle:AssignmentSection')->find($id);

        if ($assignmentSection == null) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => array(
                    'message' => 'Cet assignment_section n\'existe pas.'
                )
            )));
        }

        /** @var AssignmentService $assignmentService */
        $assignmentService = $this->container->get('app.services.assignment');
        if (!$assignmentService->checkAssignmentContext($assignmentSection->getAssignment())) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => array(
                    'message' => 'Vous ne pouvez pas modifier ce devoir.'
                )
            )));
        }

        $this->get('app.service.access_service')->tryEntity(
            $this->getUser(),
            array($assignmentSection->getAssignment()->getUser())
        );

        /** @var Section $section */
        $section = $assignmentSection->getSection();

        /** @var UploadedFile $file */
        $file = $request->files->get('file');

        if ($file == null) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => array(
                    'message' => 'Veuillez envoyer un fichier.'
                )
            )));
        }

        $fileCheck = $this->container->get('app.services.upload')->checkFile(
            $file,
            $section->getMaxSize(),
            $section->isLimitFileTypes() ? $section->getFileTypes() : null
        );
        if (!$fileCheck['success']) {
            return new Response(json_encode($fileCheck));
        }
        $uploadDirectory = $this->getParameter('upload.directory');
        $uploadedFilePattern = sprintf(
            Constants::ASSIGNMENT_SECTION_FILE_PATH_FORMAT,
            $assignmentSection->getAssignment()->getId(),
            $assignmentSection->getId()
        );
        $targetDirectory = $uploadDirectory . $uploadedFilePattern;
        if ($assignmentSection->getAnswer() != null) {
            // Delete current image
            $fileName = $targetDirectory . $assignmentSection->getAnswer();
            if (file_exists($fileName)) {
                unlink($fileName);
            }
            $assignmentSection->setAnswer(null);
        }
        $fs = new Filesystem();
        $fs->mkdir($targetDirectory);
        try {
            /** @var File $movedFile */
            $movedFile = $file->move($targetDirectory, $file->getClientOriginalName());
            unset($file);
        } catch (\Exception $e) {
            throw new HttpException(Response::HTTP_INTERNAL_SERVER_ERROR, 'Une erreur est survenue.', $e);
        }
        $assignmentSection->setAnswer($movedFile->getFilename());

        $em->flush();

        $response = array(
            'success' => true,
            'assignment' => $assignmentSection->getAssignment()
        );

        $json = $this->get('serializer')->serialize(
            $response,
            'json',
            SerializationContext::create()->setGroups(array('id', 'assignment-edit'))
        );
        return new Response($json);
    }

    /**
     * @Security("is_granted('ROLE_STUDENT')")
    /**
     * @param $id
     * @return Response
     */
    public function deleteFilesAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var AssignmentSection $assignmentSection */
        $assignmentSection = $em->getRepository('AppBundle:AssignmentSection')->find($id);

        if ($assignmentSection == null) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => array(
                    'message' => 'Cet AssignmentSection n\'existe pas.'
                )
            )));
        }

        /** @var AssignmentService $assignmentService */
        $assignmentService = $this->container->get('app.services.assignment');
        if (!$assignmentService->checkAssignmentContext($assignmentSection->getAssignment())) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => array(
                    'message' => 'Vous ne pouvez pas modifier ce devoir.'
                )
            )));
        }

        $this->get('app.service.access_service')->tryEntity(
            $this->getUser(),
            array($assignmentSection->getAssignment()->getUser())
        );

        // Delete file
        $uploadDirectory = $this->getParameter('upload.directory');
        $uploadedFilePattern = sprintf(
            Constants::ASSIGNMENT_SECTION_FILE_PATH_FORMAT,
            $assignmentSection->getAssignment()->getId(),
            $assignmentSection->getId()
        );
        $targetDirectory = $uploadDirectory . $uploadedFilePattern;
        $fileName = $targetDirectory . $assignmentSection->getAnswer();
        if (file_exists($fileName)) {
            unlink($fileName);
        }

        $assignmentSection->setAnswer(null);

        $em->flush();

        $response = array(
            'success' => true,
            'assignment' => $assignmentSection->getAssignment()
        );

        $json = $this->get('serializer')->serialize(
            $response,
            'json',
            SerializationContext::create()->setGroups(array('id', 'assignment-edit'))
        );

        return new Response($json);
    }
}
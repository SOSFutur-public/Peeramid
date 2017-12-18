<?php

namespace AppBundle\Controller;

use AppBundle\Constants;
use AppBundle\Entity\Lesson;
use AppBundle\Entity\Setting;
use AppBundle\Entity\User;
use AppBundle\Form\LessonType;
use AppBundle\Repository\SettingRepository;
use AppBundle\Service\ValidatorService;
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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


/**
 * Class LessonController
 * @package AppBundle\Controller
 * @RouteResource("lesson")
 *
 */
class LessonController extends FOSRestController
{
    /**
     * Get all Lessons
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function cgetAction()
    {
        $lesson = $this->getDoctrine()
            ->getRepository('AppBundle:Lesson')
            ->findBy(array(), array('name' => 'asc'));
        $temp = $this->get('serializer')->serialize(
            $lesson,
            'json',
            SerializationContext::create()->setGroups(array('id', 'admin-lesson-list'))
        );

        return new Response($temp);
    }

    /**
     * Get one Lesson by Id
     *
     * @param int $id
     * @return Response
     */
    public function getAction($id)
    {
        /** @var Lesson $lesson */
        $lesson = $this->getDoctrine()->getRepository('AppBundle:Lesson')->find($id);

        if ($lesson === null) {
            throw new NotFoundHttpException("Ce cours n'existe pas");
        }

        /** @var User $user */
        $user = $this->getUser();

        if ($user->getRole()->getId() !== Constants::ROLE_ADMIN) {
            $this->get('app.service.access_service')->tryEntity(
                $this->getUser(),
                $lesson->getUsers()->toArray()
            );
        }

        $json = $this->get('serializer')->serialize(
            $lesson,
            'json',
            SerializationContext::create()->setGroups(array('id', 'admin-lesson-edit'))
        );
        return new Response($json);
    }

    /**
     * @Security("is_granted('ROLE_TEACHER')")
     * @param $id
     * @return Response
     */
    public function getTeacherAction($id)
    {
        $lesson = $this->getDoctrine()->getRepository('AppBundle:Lesson')->find($id);

        $json = $this->get('serializer')->serialize(
            $lesson,
            'json',
            SerializationContext::create()->setGroups(array('id', 'teacher-lesson'))
        );
        return new Response($json);
    }

    /**
     * Create a lesson
     * @Security("is_granted('ROLE_ADMIN')")
     * @param Request $request
     * @return Response
     */
    public function postAction(Request $request)
    {
        // Get params
        $data = $request->request->all();

        // New entity
        $lesson = new Lesson();

        $form = $this->createForm(LessonType::class, $lesson);
        $form->submit($data);

        // Validate form
        /** @var ValidatorService $validatorService */
        $validatorService = $this->get('app.service.validator');
        $errors = $validatorService->validate($lesson);
        if (count($errors) > 0) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => $errors
            )));
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($lesson);
        $em->flush();

        $response = array(
            'success' => true,
            'lesson' => $lesson
        );

        $json = $this->get('serializer')->serialize(
            $response,
            'json',
            SerializationContext::create()->setGroups(array('id', 'admin-lesson-edit'))
        );
        return new Response($json);
    }

    /**
     * @param integer $id
     * @param Request $request
     * @Security("is_granted('ROLE_ADMIN')")
     * @return Response
     */
    public function postImageAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var Lesson $lesson */
        $lesson = $em->getRepository('AppBundle:Lesson')->find($id);

        if ($lesson == null) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => array(
                    'message' => 'Ce cours n\'existe pas.'
                )
            )));
        }

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

        /** @var SettingRepository $settingRepository */
        $settingRepository = $this->getDoctrine()->getRepository('AppBundle:Setting');
        /** @var Setting $maxSizeSetting */
        $maxSizeSetting = $settingRepository->find(Constants::UPLOAD_MAX_SIZE);
        $maxSize = $maxSizeSetting->getValue();

        $result = $this->container->get('app.services.upload')->checkFile($file, $maxSize, null);

        if (!$result['success']) {
            return new Response(json_encode($result));
        }

        $lesson->setImageFile($file);

        // Validate
        /** @var ValidatorService $validatorService */
        $validatorService = $this->get('app.service.validator');
        $errors = $validatorService->validate($lesson);
        if (count($errors) > 0) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => $errors
            )));
        }

        $uploadDirectory = $this->getParameter('upload.directory');
        $uploadedFilePattern = sprintf(Constants::LESSON_FILE_PATH_FORMAT, $lesson->getId());
        $targetDirectory = $uploadDirectory . $uploadedFilePattern;
        if ($lesson->getImage() != null) {
            // Delete current image
            $fileName = $targetDirectory . $lesson->getImage();
            if (file_exists($fileName)) {
                unlink($fileName);
            }
            $lesson->setImage(null);
        }
        $fs = new Filesystem();
        $fs->mkdir($targetDirectory);
        try {
            /** @var File $imageFile */
            $imageFile = $file->move($targetDirectory, $file->getClientOriginalName());
            unset($file);
        } catch (\Exception $e) {
            throw new HttpException(Response::HTTP_INTERNAL_SERVER_ERROR, 'Une erreur est survenue.', $e);
        }
        $lesson->setImage($imageFile->getFilename());

        $em->flush();

        $response = array(
            'success' => true,
            'lesson' => $lesson
        );

        $json = $this->get('serializer')->serialize(
            $response,
            'json',
            SerializationContext::create()->setGroups(array('id', 'admin-lesson-edit'))
        );
        return new Response($json);
    }

    /**
     * @param int $id
     * @return Response
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function deleteImageAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var Lesson $lesson */
        $lesson = $em->getRepository('AppBundle:Lesson')->find($id);

        if ($lesson == null) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => array(
                    'message' => 'Ce cours n\'existe pas.'
                )
            )));
        }

        // Delete file
        $uploadDirectory = $this->getParameter('upload.directory');
        $uploadedFilePattern = sprintf(Constants::LESSON_FILE_PATH_FORMAT, $lesson->getId());
        $targetDirectory = $uploadDirectory . $uploadedFilePattern;
        $fileName = $targetDirectory . $lesson->getImage();
        if (file_exists($fileName)) {
            unlink($fileName);
        }

        $lesson->setImage(null);

        $em->flush();

        $response = array(
            'success' => true,
            'lesson' => $lesson
        );

        $json = $this->get('serializer')->serialize($response, 'json', SerializationContext::create()->setGroups(array('id', 'admin-lesson-edit')));
        return new Response($json);
    }

    /**
     * @param Request $request
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_TEACHER')")
     * @return Response
     */
    public function putAction(Request $request)
    {
        // Get params
        $data = $request->request->all();

        $em = $this->getDoctrine()->getManager();

        if (!isset($data['id'])) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => array(
                    'message' => 'Formulaire invalide.'
                )
            )));
        }

        /** @var Lesson $lesson */
        $lesson = $em->getRepository('AppBundle:Lesson')->find($data['id']);

        if ($lesson == null) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => array(
                    'message' => 'Ce cours n\'existe pas.'
                )
            )));
        }

        $form = $this->createForm(LessonType::class, $lesson);
        $form->submit($data);

        // Validate form
        /** @var ValidatorService $validatorService */
        $validatorService = $this->get('app.service.validator');
        $errors = $validatorService->validate($lesson);
        if (count($errors) > 0) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => $errors
            )));
        }

        $em->flush();

        $response = array(
            'success' => true,
            'lesson' => $lesson
        );

        $json = $this->get('serializer')->serialize(
            $response,
            'json',
            SerializationContext::create()->setGroups(array('id', 'admin-lesson-edit'))
        );
        return new Response($json);
    }

    /**
     * Delete a Lesson - and dependencies (image)
     * @param integer $id
     * @Security("is_granted('ROLE_ADMIN')")
     * @return Response
     *
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        // Load lesson
        $lesson = $em->getRepository('AppBundle:Lesson')->find($id);

        if ($lesson == null) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => array(
                    'message' => 'Ce cours n\'existe pas.'
                )
            )));
        }

        $em->remove($lesson);
        $em->flush();

        return new Response(json_encode(array('success' => true)));
    }
}
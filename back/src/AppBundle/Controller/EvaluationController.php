<?php
/**
 * Created by PhpStorm.
 * User: aurore
 * Date: 04/10/2016
 * Time: 14:24
 */

namespace AppBundle\Controller;

use AppBundle\Constants;
use AppBundle\Entity\Assignment;
use AppBundle\Entity\AssignmentCriteria;
use AppBundle\Entity\AssignmentSection;
use AppBundle\Entity\Correction;
use AppBundle\Entity\CorrectionCriteria;
use AppBundle\Entity\CorrectionOpinion;
use AppBundle\Entity\CorrectionSection;
use AppBundle\Entity\Criteria;
use AppBundle\Entity\Evaluation;
use AppBundle\Entity\ExampleAssignment;
use AppBundle\Entity\Section;
use AppBundle\Entity\Setting;
use AppBundle\Entity\SubjectFile;
use AppBundle\Entity\Trapezium;
use AppBundle\Entity\User;
use AppBundle\Form\EvaluationCorrectionType;
use AppBundle\Form\EvaluationCreationType;
use AppBundle\Form\EvaluationStatsType;
use AppBundle\Form\EvaluationType;
use AppBundle\Repository\AssignmentRepository;
use AppBundle\Repository\EvaluationRepository;
use AppBundle\Repository\SettingRepository;
use AppBundle\Service\EvaluationService;
use AppBundle\Service\StatsService;
use AppBundle\Service\ValidatorService;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\FOSRestController;
use JMS\Serializer\SerializationContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class EvaluationController
 * @package AppBundle\Controller
 * @RouteResource("Evaluation")
 */
class EvaluationController extends FOSRestController
{
    /**
     * Get an evaluation by Id
     *
     * @param integer $id
     * @return Response
     */
    public function getAction($id)
    {
        /** @var Evaluation $evaluation */
        $evaluation = $this->getDoctrine()->getRepository('AppBundle:Evaluation')->find($id);

        if ($evaluation === null) {
            throw new NotFoundHttpException("Cette évaluation n'existe pas");
        }

        $this->get('app.service.access_service')->tryEntity(
            $this->getUser(),
            array($evaluation->getTeacher())
        );

        $json = $this->get('serializer')->serialize(
            $evaluation,
            'json',
            SerializationContext::create()->setGroups(array('id', 'evaluation-edit'))
        );
        return new Response($json);
    }

    /**
     * @param $id
     * @return Response
     */
    public function getStatsAction($id)
    {
        /** @var Evaluation $evaluation */
        $evaluation = $this->getDoctrine()->getRepository('AppBundle:Evaluation')->find($id);

        if ($evaluation === null) {
            throw new NotFoundHttpException("Cette évaluation n'existe pas");
        }

        $this->get('app.service.access_service')->tryEntity(
            $this->getUser(),
            array($evaluation->getTeacher())
        );

        /** @var Assignment $assignment */
        foreach ($evaluation->getAssignments() as $assignment) {
            /** @var Correction $correction */
            foreach ($assignment->getCorrections() as $correction) {
                /** @var CorrectionSection $correctionSection */
                foreach ($correction->getCorrectionSections() as $correctionSection) {
                    /** @var CorrectionCriteria $correctionCriteria */
                    foreach ($correctionSection->getCorrectionCriterias() as $correctionCriteria) {
                        /** @var CorrectionOpinion $correctionOpinion */
                        $correctionOpinion = $correctionCriteria->getCorrectionOpinion();
                        if ($correctionOpinion) {
                            switch ($correctionOpinion->getOpinion()) {
                                case -1:
                                    $correction->setThumbsDown($correction->getThumbsDown() + 1);
                                    break;
                                case 1:
                                    $correction->setThumbsUp($correction->getThumbsUp() + 1);
                                    break;
                            }
                        }
                    }
                }
            }
            if ($evaluation->getMarkMode()->getId() === Constants::EVALUATION_MARK_MODE_WEIGHTED_AVERAGE) {
                if ($assignment->getMark() !== null) {
                    /** @var AssignmentSection $assignmentSection */
                    foreach ($assignment->getAssignmentSections() as $assignmentSection) {
                        /** @var AssignmentCriteria $assignmentCriteria */
                        foreach ($assignmentSection->getAssignmentCriterias() as $assignmentCriteria) {
                            if ($assignmentCriteria->getCriteria()->getCriteriaType()->getId() !== Constants::CRITERIA_TYPE_COMMENT) {
                                if ($assignmentCriteria->getWeightedMark() === null) {
                                    $assignment->setWarning(true);
                                    break 2;
                                }
                            }
                        }
                    }
                }
            }
        }

        $json = $this->get('serializer')->serialize(
            $evaluation,
            'json',
            SerializationContext::create()->setGroups(array('id', 'evaluation-stats'))
        );
        return new Response($json);
    }

    /**
     * @param $id
     * @return Response
     */
    public function getStatsCriteriasAction($id)
    {
        /** @var Evaluation $evaluation */
        $evaluation = $this->getDoctrine()->getRepository('AppBundle:Evaluation')->find($id);

        if ($evaluation === null) {
            throw new NotFoundHttpException("Cette évaluation n'existe pas");
        }

        $this->get('app.service.access_service')->tryEntity(
            $this->getUser(),
            array($evaluation->getTeacher())
        );

        /** @var Assignment $assignment */
        foreach ($evaluation->getAssignments() as $assignment) {
            /** @var Correction $correction */
            foreach ($assignment->getCorrections() as $correction) {
                // Sort correctionSections by section order
                /** @var ArrayCollection $correctionSections */
                $correctionSections = $correction->getCorrectionSections();
                $iterator = $correctionSections->getIterator();
                $iterator->uasort(function ($a, $b) {
                    /** @var CorrectionSection $a */
                    /** @var CorrectionSection $b */
                    /** @var Section $sectionA */
                    $sectionA = $a->getAssignmentSection()->getSection();
                    /** @var Section $sectionB */
                    $sectionB = $b->getAssignmentSection()->getSection();
                    return ($sectionA->getOrder() < $sectionB->getOrder() ? -1 : 1);
                });
                $correctionSections = new ArrayCollection(iterator_to_array($iterator));
                $correction->removeCorrectionSections();
                /** @var CorrectionSection $correctionSection */
                foreach ($correctionSections as $correctionSection) {
                    $correction->addCorrectionSection($correctionSection);
                }
                /** @var CorrectionSection $correctionSection */
                foreach ($correction->getCorrectionSections() as $correctionSection) {
                    // Sort correctionCriterias by criteria order
                    $correctionCriterias = $correctionSection->getCorrectionCriterias();
                    $iterator = $correctionCriterias->getIterator();
                    $iterator->uasort(function ($a, $b) {
                        /** @var CorrectionCriteria $a */
                        /** @var CorrectionCriteria $b */
                        return ($a->getCriteria()->getOrder() < $b->getCriteria()->getOrder() ? -1 : 1);
                    });
                    $correctionCriterias = new ArrayCollection(iterator_to_array($iterator));
                    $correctionSection->removeCorrectionCriterias();
                    /** @var CorrectionCriteria $correctionCriteria */
                    foreach ($correctionCriterias as $correctionCriteria) {
                        $correctionSection->addCorrectionCriteria($correctionCriteria);
                    }
                    /** @var CorrectionCriteria $correctionCriteria */
                    foreach ($correctionSection->getCorrectionCriterias() as $correctionCriteria) {
                        /** @var CorrectionOpinion $correctionOpinion */
                        $correctionOpinion = $correctionCriteria->getCorrectionOpinion();
                        if ($correctionOpinion) {
                            switch ($correctionOpinion->getOpinion()) {
                                case -1:
                                    $correction->setThumbsDown($correction->getThumbsDown() + 1);
                                    break;
                                case 1:
                                    $correction->setThumbsUp($correction->getThumbsUp() + 1);
                                    break;
                            }
                        }
                    }
                }
            }

            if ($evaluation->getMarkMode()->getId() === Constants::EVALUATION_MARK_MODE_WEIGHTED_AVERAGE) {
                if ($assignment->getMark() !== null) {
                    /** @var AssignmentSection $assignmentSection */
                    foreach ($assignment->getAssignmentSections() as $assignmentSection) {
                        /** @var AssignmentCriteria $assignmentCriteria */
                        foreach ($assignmentSection->getAssignmentCriterias() as $assignmentCriteria) {
                            if ($assignmentCriteria->getCriteria()->getCriteriaType()->getId() !== Constants::CRITERIA_TYPE_COMMENT) {
                                if ($assignmentCriteria->getWeightedMark() === null) {
                                    $assignment->setWarning(true);
                                    break 2;
                                }
                            }
                        }
                    }
                }
            }
        }

        $json = $this->get('serializer')->serialize(
            $evaluation,
            'json',
            SerializationContext::create()->setGroups(array(
                'id',
                'evaluation-stats',
                'evaluation-stats-criterias'
            ))
        );
        return new Response($json);
    }

    /**
     * @param $id
     * @return Response
     */
    public function getQualityAction($id)
    {
        /** @var Evaluation $evaluation */
        $evaluation = $this->getDoctrine()->getRepository('AppBundle:Evaluation')->find($id);

        if ($evaluation === null) {
            throw new NotFoundHttpException("Cette évaluation n'existe pas");
        }

        $this->get('app.service.access_service')->tryEntity(
            $this->getUser(),
            array($evaluation->getTeacher())
        );

        /** @var StatsService $statsService */
        $statsService = $this->container->get('app.services.stats');

        $response = $statsService->getQualityStats($evaluation);

        $json = $this->get('serializer')->serialize(
            $response,
            'json',
            SerializationContext::create()->setGroups(array('id', 'name'))
        );

        return new Response($json);
    }

    /**
     * @param string $status
     * @return Response
     * @Security("is_granted('ROLE_TEACHER')")
     * @Get("/evaluations/list/{status}")
     */
    public function cgetTeacherStatusAction($status)
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!in_array($status, array('draft', 'incoming', 'in-progress', 'finished', 'archived'))) {
            throw new NotFoundHttpException('Cette route n\'existe pas.');
        }

        /** @var EvaluationRepository $evaluationRepository */
        $evaluationRepository = $this->getDoctrine()->getRepository('AppBundle:Evaluation');
        $evaluations = $evaluationRepository->findByTeacherAndStatus($user->getId(), $status);

        $json = $this->get('serializer')->serialize(
            $evaluations,
            'json',
            SerializationContext::create()->setGroups(array('id', 'evaluation-list'))
        );

        return new Response($json);
    }

    /**
     * Create a new Evaluation
     *
     * @param Request $request
     * @return Response
     * @Security("is_granted('ROLE_TEACHER')")
     *
     */
    public function postAction(Request $request)
    {
        /** @var User $teacher */
        $teacher = $this->getUser();

        $data = $request->request->all();

        $evaluation = new Evaluation();
        $form = $this->createForm(
            EvaluationCreationType::class,
            $evaluation,
            array('teacher' => $teacher->getId())
        );
        $form->submit($data);

        $evaluation->setTeacher($teacher);
        $evaluation->setActiveAssignment(false);
        $evaluation->setActiveCorrection(false);
        $evaluation->setArchived(false);
        $evaluation->setAnonymity(true);
        $evaluation->setIndividualAssignment(true);
        $evaluation->setIndividualCorrection(true);

        // Validate form
        /** @var ValidatorService $validatorService */
        $validatorService = $this->get('app.service.validator');
        $errors = $validatorService->validate($evaluation);
        if (count($errors) > 0) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => $errors
            )));
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($evaluation);
        $em->flush();

        $response = array(
            'success' => true,
            'evaluation' => $evaluation
        );

        $json = $this->get('serializer')->serialize(
            $response,
            'json',
            SerializationContext::create()->setGroups(array('id', 'evaluation-edit'))
        );

        return new Response($json);
    }

    /**
     * Update an Evaluation
     *
     * @param Request $request
     * @return Response
     */
    public function putAction(Request $request)
    {
        $data = $request->request->all();

        /** @var Evaluation $evaluation */
        $evaluation = $this->getDoctrine()->getRepository('AppBundle:Evaluation')->find($data['id']);

        /** @var User $user */
        $user = $this->getUser();

        if ($evaluation == null) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => array(
                    'message' => 'Cette évaluation n\'existe pas.'
                )
            )));
        }

        $this->get('app.service.access_service')->tryEntity(
            $user,
            array($evaluation->getTeacher())
        );

        if ($evaluation->getArchived()) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => array(
                    'message' => 'Vous ne pouvez pas modifier une évaluation archivée.'
                )
            )));
        }

        if ($evaluation->getActiveAssignment()) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => array(
                    'message' => 'Vous ne pouvez pas modifier une évaluation active.'
                )
            )));
        }

        $wasIndividualAssignment = $evaluation->getIndividualAssignment();
        $previousUsers = $evaluation->getUsers()->toArray();
        $previousGroups = $evaluation->getGroups()->toArray();

        /** @var EvaluationService $evaluationService */
        $evaluationService = $this->container->get('app.services.evaluation');

        $evaluationService->removeDeletedSections($evaluation, $data);

        $form = $this->createForm(
            EvaluationType::class,
            $evaluation,
            array('lesson' => $evaluation->getLesson()->getId())
        );
        $form->submit($data);

        // if evaluation has changed from individual to group or the other way round
        if ($evaluation->getIndividualAssignment() != $wasIndividualAssignment) {
            // Remove unneeded assignments
            $evaluationService->removeAssignments($evaluation, $wasIndividualAssignment);
            // remove users or groups
            if ($evaluation->getIndividualAssignment()) {
                $evaluation->removeAllGroups();
            } else {
                $evaluation->removeAllUsers();
            }
            $evaluation->setNumberCorrections(null);
        }

        if (count($evaluation->getUsers()) < count($previousUsers) ||
            count($evaluation->getGroups()) < count($previousGroups)) {
            $evaluation->setNumberCorrections(null);
        }

        // Validate form
        /** @var ValidatorService $validatorService */
        $validatorService = $this->get('app.service.validator');
        $errors = $validatorService->validate($evaluation);
        if (count($errors) > 0) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => $errors
            )));
        }

        if ($evaluation->getUsers()->toArray() !== $previousUsers ||
            $evaluation->getGroups()->toArray() !== $previousGroups) {
            // remove all corrections
            $evaluationService->removeCorrections($evaluation);
        }

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        $response = array(
            'success' => true,
            'evaluation' => $evaluation
        );

        $json = $this->get('serializer')->serialize(
            $response,
            'json',
            SerializationContext::create()->setGroups(array('id', 'evaluation-edit'))
        );

        return new Response($json);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function putCorrectionAction(Request $request)
    {
        $data = $request->request->all();

        /** @var Evaluation $evaluation */
        $evaluation = $this->getDoctrine()->getRepository('AppBundle:Evaluation')->find($data['id']);

        if ($evaluation == null) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => array(
                    'message' => 'Cette évaluation n\'existe pas.'
                )
            )));
        }

        /** @var User $user */
        $user = $this->getUser();
        $this->get('app.service.access_service')->tryEntity($user, array($evaluation->getTeacher()));

        if ($evaluation->getArchived()) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => array(
                    'message' => 'Vous ne pouvez pas modifier une évaluation archivée.'
                )
            )));
        }

        if ($evaluation->getActiveCorrection()) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => array(
                    'message' => 'Vous ne pouvez pas modifier une correction active.'
                )
            )));
        }

        $wasIndividualCorrection = $evaluation->getIndividualCorrection();
        $oldNumberCorrections = $evaluation->getNumberCorrections();

        /** @var EvaluationService $evaluationService */
        $evaluationService = $this->container->get('app.services.evaluation');

        $evaluationService->removeDeletedSections($evaluation, $data);

        $form = $this->createForm(EvaluationCorrectionType::class, $evaluation);
        $form->submit($data);

        // Set default trapeziums for each criteria
        /** @var StatsService $statsService */
        $statsService = $this->container->get('app.services.stats');
        /** @var Section $section */
        foreach ($evaluation->getSections() as $section) {
            /** @var Criteria $criteria */
            foreach ($section->getCriterias() as $criteria) {
                if ($criteria->getCriteriaType()->getId() !== Constants::CRITERIA_TYPE_COMMENT) {
                    if ($criteria->getTrapezium()) {
                        $trapezium = $criteria->getTrapezium();
                    } else {
                        $trapezium = new Trapezium();
                        $criteria->setTrapezium($trapezium);
                    }
                    $statsService->setDefaultTrapezium($trapezium);
                }
            }
        }

        // Validate form
        /** @var ValidatorService $validatorService */
        $validatorService = $this->get('app.service.validator');
        $errors = $validatorService->validate($evaluation);
        if (count($errors) > 0) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => $errors
            )));
        }

        if ($evaluation->getIndividualCorrection() !== $wasIndividualCorrection ||
            $evaluation->getNumberCorrections() !== $oldNumberCorrections) {
            // remove all corrections
            $evaluationService->removeCorrections($evaluation);
        }

        // Update stats
        /** @var Assignment $assignment */
        foreach ($evaluation->getAssignments() as $assignment) {
            $statsService->setStats($assignment);
        }

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        $response = array(
            'success' => true,
            'evaluation' => $evaluation
        );

        $json = $this->get('serializer')->serialize(
            $response,
            'json',
            SerializationContext::create()->setGroups(array('id', 'evaluation-edit'))
        );

        return new Response($json);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function putStatsAction(Request $request)
    {
        $data = $request->request->all();

        /** @var Evaluation $evaluation */
        $evaluation = $this->getDoctrine()->getRepository('AppBundle:Evaluation')->find($data['id']);

        if ($evaluation == null) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => array(
                    'message' => 'Cette évaluation n\'existe pas.'
                )
            )));
        }

        /** @var User $user */
        $user = $this->getUser();
        $this->get('app.service.access_service')->tryEntity($user, array($evaluation->getTeacher()));

        if ($evaluation->getArchived()) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => array(
                    'message' => 'Vous ne pouvez pas modifier une évaluation archivée.'
                )
            )));
        }

        $form = $this->createForm(EvaluationStatsType::class, $evaluation);
        $form->submit($data);

        // Validate form
        /** @var ValidatorService $validatorService */
        $validatorService = $this->get('app.service.validator');
        $errors = $validatorService->validate($evaluation);
        if (count($errors) > 0) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => $errors
            )));
        }

        // Update stats
        /** @var StatsService $statsService */
        $statsService = $this->container->get('app.services.stats');
        /** @var Assignment $assignment */
        foreach ($evaluation->getAssignments() as $assignment) {
            $statsService->setStats($assignment);
        }

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        $response = array(
            'success' => true,
            'evaluation' => $evaluation
        );

        $json = $this->get('serializer')->serialize(
            $response,
            'json',
            SerializationContext::create()->setGroups(array('id', 'evaluation-edit'))
        );

        return new Response($json);
    }

    /**
     * Activate or deactivate Assignment/Correction
     *
     * @param Request $request
     * @param string $slug
     * @return Response
     *
     * @Put("/evaluation/activate/{slug}")
     */
    public function putActivateAction(Request $request, $slug)
    {
        $data = $request->request->all();

        /** @var Evaluation $evaluation */
        $evaluation = $this->getDoctrine()->getRepository('AppBundle:Evaluation')->find($data['id']);

        if ($evaluation == null) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => array(
                    'message' => 'Cette évaluation n\'existe pas.'
                )
            )));
        }

        /** @var User $user */
        $user = $this->getUser();
        $this->get('app.service.access_service')->tryEntity($user, array($evaluation->getTeacher()));

        if ($evaluation->getArchived()) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => array(
                    'message' => 'Vous ne pouvez pas modifier une évaluation archivée.'
                )
            )));
        }

        $warning = false;

        /** @var EvaluationService $evaluationService */
        $evaluationService = $this->get('app.services.evaluation');
        switch ($slug) {
            case 'assignments':
                if ($evaluation->getActiveAssignment()) {
                    // deactivate assignment and correction
                    $evaluation->setActiveAssignment(false);
                    $evaluation->setActiveCorrection(false);
                    /** @var Assignment $assignment */
                    foreach ($evaluation->getAssignments() as $assignment) {
                        /** @var AssignmentSection $assignmentSection */
                        foreach ($assignment->getAssignmentSections() as $assignmentSection) {
                            if ($assignmentSection->getAnswer()) {
                                $warning = true;
                                break 2;
                            }
                        }
                    }
                } else {
                    $warning = null;
                    $evaluation->setActiveAssignment(true);

                    // Validate
                    /** @var ValidatorService $validatorService */
                    $validatorService = $this->get('app.service.validator');
                    $errors = $validatorService->validate($evaluation);
                    if (count($errors) > 0) {
                        return new Response(json_encode(array(
                            'success' => false,
                            'errors' => $errors
                        )));
                    }

                    // Generate assignments
                    $evaluationService->generateAssignments($evaluation);
                }
                break;
            case 'corrections':
                if ($evaluation->getActiveCorrection()) {
                    // Deactivate correction
                    $evaluation->setActiveCorrection(false);
                    /** @var Assignment $assignment */
                    foreach ($evaluation->getAssignments() as $assignment) {
                        /** @var Correction $correction */
                        foreach ($assignment->getCorrections() as $correction) {
                            /** @var CorrectionSection $correctionSection */
                            foreach ($correction->getCorrectionSections() as $correctionSection) {
                                /** @var CorrectionCriteria $correctionCriteria */
                                foreach ($correctionSection->getCorrectionCriterias() as $correctionCriteria) {
                                    if ($correctionCriteria->getMark() || $correctionCriteria->getComments()) {
                                        $warning = true;
                                        break 4;
                                    }
                                }
                            }
                        }
                    }
                } else {
                    $warning = null;
                    // Activate correction
                    $evaluation->setActiveCorrection(true);

                    // Validate
                    /** @var ValidatorService $validatorService */
                    $validatorService = $this->get('app.service.validator');
                    $errors = $validatorService->validate($evaluation);
                    if (count($errors) > 0) {
                        return new Response(json_encode(array(
                            'success' => false,
                            'errors' => $errors
                        )));
                    }

                    // Generate corrections
                    $evaluationService->generateCorrections($evaluation);
                }
                break;
            default:
                throw new NotFoundHttpException('Cette route n\'existe pas.');
        }

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        $response = array(
            'success' => true,
            'warning' => $warning,
            'evaluation' => $evaluation
        );

        $json = $this->get('serializer')->serialize(
            $response,
            'json',
            SerializationContext::create()->setGroups(array('id', 'evaluation-edit'))
        );

        return new Response($json);
    }

    /**
     * @param $id
     * @param Request $request
     * @return Response
     */
    public function postSubjectAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var Evaluation $evaluation */
        $evaluation = $em->getRepository('AppBundle:Evaluation')->find($id);

        if ($evaluation == null) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => array(
                    'message' => 'Cette évaluation n\'existe pas.'
                )
            )));
        }

        /** @var User $user */
        $user = $this->getUser();
        $this->get('app.service.access_service')->tryEntity($user, array($evaluation->getTeacher()));


        if ($evaluation->getArchived()) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => array(
                    'message' => 'Vous ne pouvez pas modifier une évaluation archivée.'
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

        $subjectFile = new SubjectFile($file, $evaluation);
        $evaluation->addSubjectFile($subjectFile);

        // Validate
        /** @var ValidatorService $validatorService */
        $validatorService = $this->get('app.service.validator');
        $errors = $validatorService->validate($evaluation);
        if (count($errors) > 0) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => $errors
            )));
        }

        $uploadDirectory = $this->getParameter('upload.directory');
        $uploadedFilePattern = sprintf(Constants::EVALUATION_SUBJECT_FILE_PATH_FORMAT, $evaluation->getId());
        $targetDirectory = $uploadDirectory . $uploadedFilePattern;
        $fs = new Filesystem();
        $fs->mkdir($targetDirectory);
        try {
            $file->move($targetDirectory, $file->getClientOriginalName());
        } catch (\Exception $e) {
            throw new HttpException(Response::HTTP_INTERNAL_SERVER_ERROR, 'Une erreur est survenue.', $e);
        }


        $em->flush();

        $response = array(
            'success' => true,
            'evaluation' => $evaluation
        );

        $json = $this->get('serializer')->serialize(
            $response,
            'json',
            SerializationContext::create()->setGroups(array('id', 'evaluation-edit'))
        );

        return new Response($json);
    }

    /**
     * @param $id
     * @param $subjectId
     * @return Response
     */
    public function deleteSubjectAction($id, $subjectId)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var SubjectFile $subjectFile */
        $subjectFile = $em->getRepository('AppBundle:SubjectFile')->find($subjectId);;

        if ($subjectFile == null) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => array(
                    'message' => 'Ce fichier n\'existe pas.'
                )
            )));
        }

        /** @var Evaluation $evaluation */
        $evaluation = $em->getRepository('AppBundle:Evaluation')->find($id);

        /** @var User $user */
        $user = $this->getUser();
        $this->get('app.service.access_service')->tryEntity($user, array($evaluation->getTeacher()));

        if ($evaluation == null) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => array(
                    'message' => 'Cette évaluation n\'existe pas.'
                )
            )));
        }

        if ($evaluation->getArchived()) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => array(
                    'message' => 'Vous ne pouvez pas modifier une évaluation archivée.'
                )
            )));
        }

        $evaluation->removeSubjectFile($subjectFile);

        $em->flush();

        $response = array(
            'success' => true,
            'evaluation' => $evaluation
        );

        $json = $this->get('serializer')->serialize(
            $response,
            'json',
            SerializationContext::create()->setGroups(array('id', 'evaluation-edit'))
        );

        return new Response($json);
    }

    /**
     * Delete an Evaluation
     *
     * @param integer $id
     * @return Response
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Evaluation $evaluation */
        $evaluation = $em->getRepository('AppBundle:Evaluation')->find($id);

        if ($evaluation == null) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => array(
                    'message' => 'Cette évaluation n\'existe pas.'
                )
            )));
        }

        /** @var User $user */
        $user = $this->getUser();
        $this->get('app.service.access_service')->tryEntity($user, array($evaluation->getTeacher()));

        $em->remove($evaluation);
        $em->flush();

        return new Response(json_encode(array('success' => true)));
    }

    /**
     * Duplicate an evaluation
     * @param $id
     * @return Response
     */
    public function postDuplicateAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Evaluation $evaluation */
        $evaluation = $em->getRepository('AppBundle:Evaluation')->find($id);

        if ($evaluation == null) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => array(
                    'message' => 'Cette évaluation n\'existe pas.'
                )
            )));
        }

        /** @var User $user */
        $user = $this->getUser();
        $this->get('app.service.access_service')->tryEntity($user, array($evaluation->getTeacher()));

        $clone = clone $evaluation;

        // Validate
        /** @var ValidatorService $validatorService */
        $validatorService = $this->get('app.service.validator');
        $errors = $validatorService->validate($evaluation);
        if (count($errors) > 0) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => $errors
            )));
        }

        $em->persist($clone);
        $em->flush();

        // Copy files
        $uploadDirectory = $this->getParameter('upload.directory');
        // SubjectFiles
        $originDirectory = $uploadDirectory .
            sprintf(Constants::EVALUATION_SUBJECT_FILE_PATH_FORMAT, $evaluation->getId());
        $targetDirectory = $uploadDirectory .
            sprintf(Constants::EVALUATION_SUBJECT_FILE_PATH_FORMAT, $clone->getId());
        $fs = new Filesystem();
        $fs->mkdir($targetDirectory);
        /** @var SubjectFile $subjectFile */
        foreach ($evaluation->getSubjectFiles() as $subjectFile) {
            $fs->copy(
                $originDirectory . $subjectFile->getFileName(),
                $targetDirectory . $subjectFile->getFileName(),
                true
            );
        }
        // ExampleAssignments
        $originDirectory = $uploadDirectory .
            sprintf(Constants::EVALUATION_EXAMPLE_ASSIGNMENT_FILE_PATH_FORMAT, $evaluation->getId());
        $targetDirectory = $uploadDirectory .
            sprintf(Constants::EVALUATION_EXAMPLE_ASSIGNMENT_FILE_PATH_FORMAT, $clone->getId());
        $fs = new Filesystem();
        $fs->mkdir($targetDirectory);
        /** @var ExampleAssignment $exampleAssignment */
        foreach ($evaluation->getExampleAssignments() as $exampleAssignment) {
            $fs->copy(
                $originDirectory . $exampleAssignment->getFileName(),
                $targetDirectory . $exampleAssignment->getFileName(),
                true
            );
        }

        $response = array(
            'success' => true,
            'evaluation' => $clone
        );

        $json = $this->get('serializer')->serialize(
            $response,
            'json',
            SerializationContext::create()->setGroups(array('id', 'evaluation-edit'))
        );

        return new Response($json);
    }

    /**
     * @param integer $evaluationId
     * @return Response
     */
    public function getAttributionAction($evaluationId)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Evaluation $evaluation */
        $evaluation = $em->getRepository('AppBundle:Evaluation')->find($evaluationId);

        if ($evaluation === null) {
            throw new NotFoundHttpException("Cette évaluation n'existe pas");
        }

        /** @var AssignmentRepository $assignmentRepository */
        $assignmentRepository = $this->getDoctrine()->getRepository('AppBundle:Assignment');
        $assignments = $assignmentRepository->findBy(array('evaluation' => $evaluationId));

        /** @var User $user */
        $user = $this->getUser();
        $this->get('app.service.access_service')->tryEntity($user, array($evaluation->getTeacher()));

        $json = $this->get('serializer')->serialize(
            $assignments,
            'json',
            SerializationContext::create()->setGroups(array('id', 'evaluation-attribution'))
        );

        return new Response($json);
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @Put("/evaluation/reset-attribution")
     */
    public function resetAttributionAction(Request $request)
    {
        $data = $request->request->all();

        /** @var Evaluation $evaluation */
        $evaluation = $this->getDoctrine()->getRepository('AppBundle:Evaluation')->find($data['id']);

        if ($evaluation == null) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => array(
                    'message' => 'Cette évaluation n\'existe pas.'
                )
            )));
        }

        /** @var User $user */
        $user = $this->getUser();
        $this->get('app.service.access_service')->tryEntity($user, array($evaluation->getTeacher()));

        if ($evaluation->getArchived()) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => array(
                    'message' => 'Vous ne pouvez pas modifier une évaluation archivée.'
                )
            )));
        }

        if ($evaluation->getActiveCorrection()) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => array(
                    'message' => 'Vous ne pouvez pas modifier une correction active.'
                )
            )));
        }

        $evaluation->setActiveCorrection(true);

        // Validate
        /** @var ValidatorService $validatorService */
        $validatorService = $this->get('app.service.validator');
        $errors = $validatorService->validate($evaluation);
        if (count($errors) > 0) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => $errors
            )));
        }

        /** @var EvaluationService $evaluationService */
        $evaluationService = $this->container->get('app.services.evaluation');
        $evaluationService->resetAttribution($evaluation);

        $evaluation->setActiveCorrection(false);

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        /** @var AssignmentRepository $assignmentRepository */
        $assignmentRepository = $this->getDoctrine()->getRepository('AppBundle:Assignment');
        $assignments = $assignmentRepository->findBy(array('evaluation' => $evaluation));

        $response = array(
            'success' => true,
            'assignments' => $assignments
        );

        $json = $this->get('serializer')->serialize(
            $response,
            'json',
            SerializationContext::create()->setGroups(array('id', 'evaluation-attribution'))
        );

        return new Response($json);
    }

    /**
     * @param int $id
     * @param Request $request
     * @return Response
     */
    public function postExampleAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var Evaluation $evaluation */
        $evaluation = $em->getRepository('AppBundle:Evaluation')->find($id);

        if ($evaluation == null) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => array(
                    'message' => 'Cette évaluation n\'existe pas.'
                )
            )));
        }

        /** @var User $user */
        $user = $this->getUser();
        $this->get('app.service.access_service')->tryEntity($user, array($evaluation->getTeacher()));

        if ($evaluation->getArchived()) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => array(
                    'message' => 'Vous ne pouvez pas modifier une évaluation archivée.'
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

        $exampleAssignment = new ExampleAssignment($file, $evaluation);
        $evaluation->addExampleAssignment($exampleAssignment);

        // Validate
        /** @var ValidatorService $validatorService */
        $validatorService = $this->get('app.service.validator');
        $errors = $validatorService->validate($evaluation);
        if (count($errors) > 0) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => $errors
            )));
        }

        $uploadDirectory = $this->getParameter('upload.directory');
        $uploadedFilePattern = sprintf(
            Constants::EVALUATION_EXAMPLE_ASSIGNMENT_FILE_PATH_FORMAT,
            $evaluation->getId()
        );
        $targetDirectory = $uploadDirectory . $uploadedFilePattern;
        $fs = new Filesystem();
        $fs->mkdir($targetDirectory);
        try {
            $file->move($targetDirectory, $file->getClientOriginalName());
        } catch (\Exception $e) {
            throw new HttpException(Response::HTTP_INTERNAL_SERVER_ERROR, 'Une erreur est survenue.', $e);
        }

        $em->flush();

        $response = array(
            'success' => true,
            'evaluation' => $evaluation
        );

        $json = $this->get('serializer')->serialize(
            $response,
            'json',
            SerializationContext::create()->setGroups(array('id', 'evaluation-edit'))
        );

        return new Response($json);
    }

    /**
     * @param int $id
     * @param int $exampleId
     * @return Response
     */
    public function deleteExampleAction($id, $exampleId)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var ExampleAssignment $exampleAssignment */
        $exampleAssignment = $em->getRepository('AppBundle:ExampleAssignment')->find($exampleId);

        if ($exampleAssignment == null) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => array(
                    'message' => 'Ce devoir exemple n\'existe pas.'
                )
            )));
        }

        /** @var Evaluation $evaluation */
        $evaluation = $em->getRepository('AppBundle:Evaluation')->find($id);

        if ($evaluation == null) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => array(
                    'message' => 'Cette évaluation n\'existe pas.'
                )
            )));
        }

        /** @var User $user */
        $user = $this->getUser();
        $this->get('app.service.access_service')->tryEntity($user, array($evaluation->getTeacher()));

        if ($evaluation->getArchived()) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => array(
                    'message' => 'Vous ne pouvez pas modifier une évaluation archivée.'
                )
            )));
        }

        $evaluation->removeExampleAssignment($exampleAssignment);

        $em->flush();

        $response = array(
            'success' => true,
            'evaluation' => $evaluation
        );

        $json = $this->get('serializer')->serialize(
            $response,
            'json',
            SerializationContext::create()->setGroups(array('id', 'evaluation-edit'))
        );

        return new Response($json);
    }

    /**
     * @param $id
     * @return Response
     */
    public function getChartsAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var Evaluation $evaluation */
        $evaluation = $em->getRepository('AppBundle:Evaluation')->find($id);

        if ($evaluation == null) {
            throw new NotFoundHttpException('Cette évaluation n\'existe pas.');
        }

        /** @var User $user */
        $user = $this->getUser();
        $this->get('app.service.access_service')->tryEntity($user, array($evaluation->getTeacher()));

        /** @var StatsService $statsService */
        $statsService = $this->container->get('app.services.stats');

        $statsService->getCharts($evaluation);

        $json = $this->get('serializer')->serialize(
            $evaluation,
            'json',
            SerializationContext::create()->setGroups(array('id', 'criteria-charts'))
        );

        return new Response($json);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function putArchiveAction(Request $request)
    {
        $data = $request->request->all();

        /** @var Evaluation $evaluation */
        $evaluation = $this->getDoctrine()->getRepository('AppBundle:Evaluation')->find($data['id']);

        if ($evaluation == null) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => array(
                    'message' => 'Cette évaluation n\'existe pas.'
                )
            )));
        }

        /** @var User $user */
        $user = $this->getUser();
        $this->get('app.service.access_service')->tryEntity($user, array($evaluation->getTeacher()));

        if ($evaluation->getArchived()) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => array(
                    'message' => 'Cette évaluation est déjà archivée.'
                )
            )));
        }

        $now = new \DateTime();

        if (!$evaluation->getActiveAssignment() || !$evaluation->getActiveCorrection() ||
            $now < $evaluation->getDateEndCorrection() || $now < $evaluation->getDateEndOpinion()) {
            return new Response(json_encode(array(
                'success' => false,
                'errors' => array(
                    'message' => 'Vous ne pouvez pas encore archiver cette correction.'
                )
            )));
        }

        $evaluation->setArchived(true);

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        $response = array(
            'success' => true,
            'evaluation' => $evaluation
        );

        $json = $this->get('serializer')->serialize(
            $response,
            'json',
            SerializationContext::create()->setGroups(array('id', 'evaluation-edit'))
        );

        return new Response($json);
    }
}
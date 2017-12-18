<?php
/**
 * Created by PhpStorm.
 * User: Nicolas
 * Date: 05/10/2017
 * Time: 12:25
 */

namespace AppBundle\Service;


use AppBundle\Constants;
use AppBundle\Entity\AssignmentSection;
use AppBundle\Entity\CorrectionCriteria;
use AppBundle\Entity\Criteria;
use AppBundle\Entity\CriteriaChoice;
use AppBundle\Entity\ExampleAssignment;
use AppBundle\Entity\Lesson;
use AppBundle\Entity\Section;
use AppBundle\Entity\SectionType;
use AppBundle\Entity\SubjectFile;
use AppBundle\Entity\User;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DoctrineListener implements EventSubscriber
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
            'onFlush',
            'preRemove'
        );
    }

    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $em = $eventArgs->getEntityManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            $changeSet = $uow->getEntityChangeSet($entity);
            if ($entity instanceof Section) {
                if (array_key_exists('section_type', $changeSet)) {
                    /** @var SectionType $oldSectionType */
                    $oldSectionType = $changeSet['section_type'][0];
                    /** @var AssignmentSection $assignmentSection */
                    foreach ($entity->getAssignmentSections() as $assignmentSection) {
                        if ($oldSectionType->getId() === Constants::SECTION_TYPE_FILE) {
                            // Delete old file
                            if ($assignmentSection->getAnswer()) {
                                // Delete file
                                $uploadDirectory = $this->container->getParameter('upload.directory');
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
                            }
                        }
                        $assignmentSection->setAnswer(null);
                        $metaData = $em->getClassMetadata('AppBundle\Entity\AssignmentSection');
                        $uow->recomputeSingleEntityChangeSet($metaData, $assignmentSection);
                    }
                }
            }
            /** @var Criteria $entity */
            if ($entity instanceof Criteria) {
                if (array_key_exists('criteria_type', $changeSet) ||
                    array_key_exists('mark_max', $changeSet) ||
                    array_key_exists('precision', $changeSet)) {
                    /** @var CorrectionCriteria $correctionCriteria */
                    foreach ($entity->getCorrectionCriterias() as $correctionCriteria) {
                        $correctionCriteria->setMark(null);
                        $correctionCriteria->setComments(null);
                        $correctionCriteria->setReliability(null);
                        $correctionCriteria->setRecalculatedReliability(null);
                        $metaData = $em->getClassMetadata('AppBundle\Entity\CorrectionCriteria');
                        $uow->recomputeSingleEntityChangeSet($metaData, $correctionCriteria);
                    }
                }
            }
            /** @var CriteriaChoice $entity */
            if ($entity instanceof CriteriaChoice) {
                if (array_key_exists('mark', $changeSet)) {
                    foreach ($entity->getCriteria()->getCorrectionCriterias() as $correctionCriteria) {
                        $correctionCriteria->setMark(null);
                        $correctionCriteria->setReliability(null);
                        $correctionCriteria->setRecalculatedReliability(null);
                        $metaData = $em->getClassMetadata('AppBundle\Entity\CorrectionCriteria');
                        $uow->recomputeSingleEntityChangeSet($metaData, $correctionCriteria);
                    }
                }
            }
        }
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if ($entity instanceof AssignmentSection) {
            /** @var AssignmentSection $assignmentSection */
            $assignmentSection = $entity;
            if ($assignmentSection->getSection()->getSectionType()->getId() == Constants::SECTION_TYPE_FILE) {
                if ($assignmentSection->getAnswer()) {
                    // Delete file
                    $uploadDirectory = $this->container->getParameter('upload.directory');
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
                }
            }
        }
        if ($entity instanceof SubjectFile) {
            /** @var SubjectFile $entity */
            $uploadDirectory = $this->container->getParameter('upload.directory');
            $uploadedFilePattern = sprintf(
                Constants::EVALUATION_SUBJECT_FILE_PATH_FORMAT,
                $entity->getEvaluation()->getId()
            );
            $targetDirectory = $uploadDirectory . $uploadedFilePattern;
            $fileName = $targetDirectory . $entity->getFileName();
            if (file_exists($fileName)) {
                unlink($fileName);
            }
        }
        if ($entity instanceof ExampleAssignment) {
            /** @var ExampleAssignment $entity */
            $uploadDirectory = $this->container->getParameter('upload.directory');
            $uploadedFilePattern = sprintf(
                Constants::EVALUATION_EXAMPLE_ASSIGNMENT_FILE_PATH_FORMAT,
                $entity->getEvaluation()->getId()
            );
            $targetDirectory = $uploadDirectory . $uploadedFilePattern;
            $fileName = $targetDirectory . $entity->getFileName();
            if (file_exists($fileName)) {
                unlink($fileName);
            }
        }
        if ($entity instanceof User) {
            /** @var User $entity */
            $uploadDirectory = $this->container->getParameter('upload.directory');
            $uploadedFilePattern = sprintf(
                Constants::USER_FILE_PATH_FORMAT,
                $entity->getId()
            );
            $targetDirectory = $uploadDirectory . $uploadedFilePattern;
            $fileName = $targetDirectory . $entity->getImage();
            if (file_exists($fileName)) {
                unlink($fileName);
            }
        }
        if ($entity instanceof Lesson) {
            /** @var Lesson $entity */
            $uploadDirectory = $this->container->getParameter('upload.directory');
            $uploadedFilePattern = sprintf(
                Constants::LESSON_FILE_PATH_FORMAT,
                $entity->getId()
            );
            $targetDirectory = $uploadDirectory . $uploadedFilePattern;
            $fileName = $targetDirectory . $entity->getImage();
            if (file_exists($fileName)) {
                unlink($fileName);
            }
        }
        /** @var CriteriaChoice $entity */
        if ($entity instanceof CriteriaChoice) {
            foreach ($entity->getCriteria()->getCorrectionCriterias() as $correctionCriteria) {
                $correctionCriteria->setMark(null);
                $correctionCriteria->setReliability(null);
                $correctionCriteria->setRecalculatedReliability(null);
            }
        }
    }
}
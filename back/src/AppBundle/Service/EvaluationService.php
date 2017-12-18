<?php

namespace AppBundle\Service;

use AppBundle\Entity\Assignment;
use AppBundle\Entity\AssignmentCriteria;
use AppBundle\Entity\AssignmentSection;
use AppBundle\Entity\Correction;
use AppBundle\Entity\CorrectionCriteria;
use AppBundle\Entity\CorrectionOpinion;
use AppBundle\Entity\CorrectionSection;
use AppBundle\Entity\Criteria;
use AppBundle\Entity\Evaluation;
use AppBundle\Entity\Group;
use AppBundle\Entity\Section;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;


/**
 * Class EvaluationService
 * @package AppBundle\Service
 *
 */
class EvaluationService
{
    private $em;
    private $logger;

    /**
     * EvaluationService constructor.
     * @param LoggerInterface $logger
     * @param EntityManager $em
     */
    public function __construct(LoggerInterface $logger, EntityManager $em)
    {
        $this->logger = $logger;
        $this->em = $em;
    }

    /**
     * Create "assignments" for all evaluation's authors
     * @param Evaluation $evaluation
     */
    public function generateAssignments(Evaluation $evaluation)
    {
        // Valid only for
        if ($evaluation->getActiveAssignment()) {
            $this->removeUnneededAssignments($evaluation);
            // Check evaluation mode (individual / group)
            if ($evaluation->getIndividualAssignment()) {
                if ($evaluation->getUsers()) {
                    /** @var User $user */
                    foreach ($evaluation->getUsers() as $user) {
                        // Check if a assignment already exist
                        $assignments = $this->em->getRepository('AppBundle:Assignment')->findBy(array(
                            'evaluation' => $evaluation,
                            'user' => $user
                        ), array());
                        if (!$assignments || empty($assignments)) {
                            // No assignment - create new one
                            $this->createAssignmentForStudent($evaluation, $user);
                        } else {
                            foreach ($assignments as $assignment) {
                                $this->regenerateSectionsForAssignment($assignment);
                            }
                        }
                    }
                }
            } else {
                if ($evaluation->getGroups()) {
                    foreach ($evaluation->getGroups() as $group) {
                        // Check if a assignment already exist
                        $assignments = $this->em->getRepository('AppBundle:Assignment')->findBy(array(
                            'evaluation' => $evaluation,
                            'group' => $group
                        ), array());
                        if (!$assignments || empty($assignments)) {
                            // No assignment - create new one
                            $this->createAssignmentForGroup($evaluation, $group);
                        } else {
                            foreach ($assignments as $assignment) {
                                $this->regenerateSectionsForAssignment($assignment);
                            }
                        }
                    }
                }
            }
            // Apply db queries
            $this->em->flush();
        }
    }

    /**
     * @param Evaluation $evaluation
     */
    private function removeUnneededAssignments(Evaluation $evaluation)
    {
        if ($evaluation->getIndividualAssignment()) {
            if ($evaluation->getUsers()) {
                // Remove no more needed assignments
                /** @var Assignment $assignment */
                foreach ($evaluation->getAssignments() as $assignment) {
                    $found = false;
                    foreach ($evaluation->getUsers() as $student) {
                        /** @var User $student */
                        if ($assignment->getUser() && $assignment->getUser()->getId() === $student->getId()) {
                            $found = true;
                            break;
                        }
                    }
                    // Delete "assignment"
                    if (!$found) {
                        $this->em->remove($assignment);
                    }
                }
            } else {
                // remove all assignments
                $evaluation->getAssignments()->clear();
            }
            $this->em->flush();
        } else {
            if ($evaluation->getGroups()) {
                // Remove no more needed assignments
                /** @var Assignment $assignment */
                foreach ($evaluation->getAssignments() as $assignment) {
                    $found = false;
                    foreach ($evaluation->getGroups() as $group) {
                        /** @var Group $group */
                        if ($assignment->getGroup() && $assignment->getGroup()->getId() === $group->getId()) {
                            $found = true;
                            break;
                        }
                    }
                    // Delete "assignment"
                    if (!$found) {
                        $this->em->remove($assignment);
                    }
                }
            } else {
                // remove all assignments
                $evaluation->getAssignments()->clear();
            }
            // Apply db queries
            $this->em->flush();
        }
    }

    /**
     * Create new "assignment" in db
     * @param Evaluation $evaluation
     * @param User $user
     */
    private function createAssignmentForStudent(Evaluation $evaluation, User $user)
    {
        // No assignment - create new one
        $assignment = new Assignment();
        $assignment->setEvaluation($evaluation);
        $assignment->setUser($user);
        $this->addSectionsToAssignment($assignment);
        $this->em->persist($assignment);
    }

    /**
     * @param Assignment $assignment
     */
    private function addSectionsToAssignment(Assignment $assignment)
    {
        if ($assignment->getEvaluation()->getSections()) {
            /** @var Section $section */
            foreach ($assignment->getEvaluation()->getSections() as $section) {
                $this->createAssignmentSection($assignment, $section);
            }
        }
    }

    /**
     * @param Assignment $assignment
     * @param Section $section
     */
    private function createAssignmentSection(Assignment $assignment, Section $section)
    {
        $assignmentSection = new AssignmentSection();
        $assignmentSection->setSection($section);
        $assignment->addAssignmentSection($assignmentSection);
    }

    /**
     * @param Assignment $assignment
     */
    private function regenerateSectionsForAssignment(Assignment $assignment)
    {
        /** @var Section $section */
        foreach ($assignment->getEvaluation()->getSections() as $section) {
            $found = false;
            /** @var AssignmentSection $assignmentSection */
            foreach ($assignment->getAssignmentSections() as $assignmentSection) {
                if ($assignmentSection->getSection() === $section) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $this->createAssignmentSection($assignment, $section);
            }
        }
    }

    /**
     * Create new group in db
     * @param Evaluation $evaluation
     * @param Group $group
     */
    private function createAssignmentForGroup(Evaluation $evaluation, Group $group)
    {
        // No assignment - create new one
        $assignment = new Assignment();
        $assignment->setEvaluation($evaluation);
        $assignment->setGroup($group);
        $this->addSectionsToAssignment($assignment);
        $this->em->persist($assignment);
    }

    /**
     * @param Evaluation $evaluation
     */
    public function resetAttribution(Evaluation $evaluation)
    {
        // Remove corrections (cascade dependencies)
        $this->removeCorrections($evaluation);

        // Generate new corrections
        $this->generateCorrections($evaluation);
        $this->em->flush();
    }

    /**
     * @param Evaluation $evaluation
     */
    public function removeCorrections(Evaluation $evaluation)
    {
        $evaluation->setAssignmentAverage(null);
        /** @var Assignment $assignment */
        foreach ($evaluation->getAssignments() as $assignment) {
            /** @var Correction $correction */
            foreach ($assignment->getCorrections() as $correction) {
                $assignment->removeCorrection($correction);
            }
            $assignment->setRawMark(null);
            $assignment->setWeightedMark(null);
            $assignment->setReliability(null);
            $assignment->setMark(null);
            $assignment->setStandardDeviation(null);
        }
        $this->em->flush();
    }

    /**
     * Create
     * @param Evaluation $evaluation
     */
    public function generateCorrections(Evaluation $evaluation)
    {
        $this->regenerateAssignmentCriterias($evaluation);

        // Check criterias for existing corrections
        /** @var Assignment $assignment */
        foreach ($evaluation->getAssignments() as $assignment) {
            /** @var Correction $correction */
            foreach ($assignment->getCorrections() as $correction) {
                $this->regenerateCriteriasForCorrection($correction);
            }
        }

        // Check if corrections already exist
        /** @var bool $correctionsExist */
        $correctionsExist = false;
        /** @var Assignment $assignment */
        foreach ($evaluation->getAssignments() as $assignment) {
            if (count($assignment->getCorrections()) > 0) {
                $correctionsExist = true;
            }
            break;
        }

        if (!$correctionsExist) {
            $assignments = $evaluation->getAssignments()->toArray();

            // Generate teacher corrections
            foreach ($assignments as $assignment) {
                $this->createCorrectionForUser($evaluation->getTeacher(), $assignment);
            }

            $numberAssignments = count($assignments);
            // Random sort assignments
            shuffle($assignments);

            if ($evaluation->getIndividualAssignment()) {
                if ($evaluation->getIndividualCorrection()) {
                    // Individual assignment, individual correction
                    /** @var Assignment $assignment */
                    foreach ($assignments as $i => $assignment) {
                        for ($j = 0; $j < $evaluation->getNumberCorrections(); $j++) {
                            $correctorIndex = ($i + $j + 1) % $numberAssignments;
                            /** @var Assignment $assignmentCorrector */
                            $assignmentCorrector = $assignments[$correctorIndex];
                            $corrector = $assignmentCorrector->getUser();
                            $this->createCorrectionForUser($corrector, $assignment);
                        }
                    }
                } else {
                    // Individual assignment, group correction

                }
            } else {
                if ($evaluation->getIndividualCorrection()) {
                    // Group assignment, individual correction
                    $i = 0;
                    /** @var Assignment $assignment */
                    foreach ($assignments as $assignment) {
                        /** @var Group $group */
                        $group = $assignment->getGroup();
                        /** @var User $user */
                        foreach ($group->getUsers() as $user) {
                            $correctedAssignments = array();
                            for ($j = 0; $j < $evaluation->getNumberCorrections(); $j++) {
                                do {
                                    /** @var Assignment $correctedAssignment */
                                    $correctedAssignment = $assignments[$i++ % $numberAssignments];
                                } while ($correctedAssignment === $assignment || in_array($correctedAssignment, $correctedAssignments));
                                $this->createCorrectionForUser($user, $correctedAssignment);
                                $correctedAssignments[] = $correctedAssignment;
                            }
                        }
                    }
                } else {
                    // Group assignment, group correction
                    foreach ($assignments as $i => $assignment) {
                        for ($j = 0; $j < $evaluation->getNumberCorrections(); $j++) {
                            $correctorIndex = ($i + $j + 1) % count($assignments);
                            /** @var Assignment $assignmentCorrector */
                            $assignmentCorrector = $assignments[$correctorIndex];
                            $corrector = $assignmentCorrector->getGroup();
                            $this->createCorrectionForGroup($corrector, $assignment);
                        }
                    }
                }
            }
        }

        // Apply db queries
        $this->em->flush();
    }

    /**
     * @param Evaluation $evaluation
     */
    private function regenerateAssignmentCriterias(Evaluation $evaluation)
    {
        /** @var Assignment $assignment */
        foreach ($evaluation->getAssignments() as $assignment) {
            /** @var AssignmentSection $assignmentSection */
            foreach ($assignment->getAssignmentSections() as $assignmentSection) {
                /** @var Criteria $criteria */
                foreach ($assignmentSection->getSection()->getCriterias() as $criteria) {
                    $found = false;
                    /** @var AssignmentCriteria $assignmentCriteria */
                    foreach ($assignmentSection->getAssignmentCriterias() as $assignmentCriteria) {
                        if ($assignmentCriteria->getCriteria() === $criteria) {
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        $assignmentCriteria = new AssignmentCriteria();
                        $assignmentCriteria->setCriteria($criteria);
                        $assignmentSection->addAssignmentCriteria($assignmentCriteria);
                    }
                }
            }
        }
        $this->em->flush();
    }

    /**
     * @param Correction $correction
     */
    private function regenerateCriteriasForCorrection(Correction $correction)
    {
        /** @var AssignmentSection $assignmentSection */
        foreach ($correction->getAssignment()->getAssignmentSections() as $assignmentSection) {
            $correctionSectionFound = false;
            /** @var CorrectionSection $correctionSection */
            foreach ($correction->getCorrectionSections() as $correctionSection) {
                if ($correctionSection->getAssignmentSection() === $assignmentSection) {
                    $correctionSectionFound = true;
                    break;
                }
            }
            if (!$correctionSectionFound) {
                $this->createCorrectionSection($correction, $assignmentSection);
            }

            /** @var CorrectionSection $correctionSection */
            foreach ($correction->getCorrectionSections() as $correctionSection) {
                /** @var Criteria $criteria */
                foreach ($correctionSection->getAssignmentSection()->getSection()->getCriterias() as $criteria) {
                    $found = false;
                    /** @var CorrectionCriteria $correctionCriteria */
                    foreach ($correctionSection->getCorrectionCriterias() as $correctionCriteria) {
                        if ($correctionCriteria->getCriteria() === $criteria) {
                            $found = true;
                            if (!$correctionCriteria->getCorrectionOpinion()) {
                                $correctionCriteria->setCorrectionOpinion(new CorrectionOpinion());
                            }
                            break;
                        }
                    }
                    if (!$found) {
                        $this->createCorrectionCriteria($correctionSection, $criteria);
                    }
                }
            }
        }
    }

    /**
     * @param Correction $correction
     * @param AssignmentSection $assignmentSection
     */
    private function createCorrectionSection(Correction $correction, AssignmentSection $assignmentSection)
    {
        $correctionSection = new CorrectionSection();
        $correctionSection->setAssignmentSection($assignmentSection);
        $correction->addCorrectionSection($correctionSection);
        foreach ($assignmentSection->getSection()->getCriterias() as $criteria) {
            $this->createCorrectionCriteria($correctionSection, $criteria);
        }
    }

    /**
     * @param CorrectionSection $correctionSection
     * @param Criteria $criteria
     */
    private function createCorrectionCriteria(CorrectionSection $correctionSection, Criteria $criteria)
    {
        $correctionCriteria = new CorrectionCriteria();
        $correctionCriteria->setCriteria($criteria);
        $correctionSection->addCorrectionCriteria($correctionCriteria);
        $correctionOpinion = new CorrectionOpinion();
        $correctionCriteria->setCorrectionOpinion($correctionOpinion);
    }

    /**
     * @param User $user
     * @param Assignment $assignment
     */
    private function createCorrectionForUser(User $user, Assignment $assignment)
    {
        // Check for already exists
        $corrections = $this->em->getRepository('AppBundle:Correction')->findBy(array(
            'assignment' => $assignment,
            'user' => $user
        ));
        if (!$corrections || empty($corrections)) {
            $correction = new Correction();
            $correction->setUser($user);
            $assignment->addCorrection($correction);
            $this->addSectionsToCorrection($correction);
            $this->em->persist($correction);
        }
    }

    /**
     * @param Correction $correction
     */
    private function addSectionsToCorrection(Correction $correction)
    {
        /** @var AssignmentSection $assignmentSection */
        foreach ($correction->getAssignment()->getAssignmentSections() as $assignmentSection) {
            $this->createCorrectionSection($correction, $assignmentSection);
        }
    }

    /**
     * @param Group $group
     * @param Assignment $assignment
     */
    private function createCorrectionForGroup(Group $group, Assignment $assignment)
    {
        // Check for already exists
        $corrections = $this->em->getRepository('AppBundle:Correction')->findBy(array(
            'assignment' => $assignment,
            'group' => $group
        ));
        if (!$corrections || empty($corrections)) {
            // No assignment - create new one
            $correction = new Correction();
            $correction->setGroup($group);
            $assignment->addCorrection($correction);
            $this->addSectionsToCorrection($correction);
            $this->em->persist($correction);
        }
    }

    /**
     * @param Evaluation $evaluation
     * @param boolean $individual
     */
    public function removeAssignments(Evaluation $evaluation, $individual)
    {
        $assignments = $evaluation->getAssignments();
        if ($individual) {
            // Assignment was individual and is now group
            // Remove all individual assignments
            /** @var Assignment $assignment */
            foreach ($assignments as $assignment) {
                if ($assignment->getUser()) {
                    $this->em->remove($assignment);
                }
            }
        } else {
            // Assignment was group and is now individual
            // Remove all group assignments
            /** @var Assignment $assignment */
            foreach ($assignments as $assignment) {
                if ($assignment->getGroup()) {
                    $this->em->remove($assignment);
                }
            }
        }
    }

    /**
     * @param Evaluation $evaluation
     * @param array $data
     */
    public function removeDeletedSections(Evaluation $evaluation, array $data)
    {
        $sectionIds = array();
        if (array_key_exists('sections', $data)) {
            foreach ($data['sections'] as $section) {
                if (array_key_exists('id', $section)) {
                    $sectionIds[] = $section['id'];
                }
            }
            /** @var Section $section */
            foreach ($evaluation->getSections() as $section) {
                if (!in_array($section->getId(), $sectionIds)) {
                    $evaluation->removeSection($section);
                }
            }
        }
    }

    /**
     * @param Evaluation $evaluation
     * @param array $data
     */
    public function removeDeletedCriterias(Evaluation $evaluation, array $data)
    {
        $criteriaIds = array();
        if (array_key_exists('sections', $data)) {
            foreach ($data['sections'] as $section) {
                if (array_key_exists('criterias', $section)) {
                    foreach ($section['criterias'] as $criteria) {
                        if (array_key_exists('id', $criteria)) {
                            $criteriaIds[] = $criteria['id'];
                        }
                    }
                }
            }
        }
        /** @var Section $section */
        foreach ($evaluation->getSections() as $section) {
            /** @var Criteria $criteria */
            foreach ($section->getCriterias() as $criteria) {
                if (!in_array($criteria->getId(), $criteriaIds)) {
                    $section->removeCriteria($criteria);
                }
            }
        }
    }
}
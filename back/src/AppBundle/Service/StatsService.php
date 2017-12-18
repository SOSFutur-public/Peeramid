<?php

namespace AppBundle\Service;

use AppBundle\Constants;
use AppBundle\Entity\Assignment;
use AppBundle\Entity\AssignmentCriteria;
use AppBundle\Entity\AssignmentSection;
use AppBundle\Entity\Correction;
use AppBundle\Entity\CorrectionCriteria;
use AppBundle\Entity\CorrectionSection;
use AppBundle\Entity\Criteria;
use AppBundle\Entity\CriteriaChoice;
use AppBundle\Entity\Evaluation;
use AppBundle\Entity\Group;
use AppBundle\Entity\Section;
use AppBundle\Entity\Trapezium;
use AppBundle\Entity\User;
use AppBundle\Repository\CorrectionCriteriaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;


/**
 * Class StatsService
 * @package AppBundle\Service
 *
 */
class StatsService
{

    private $em;
    private $logger;

    /**
     * StatsService constructor.
     * @param LoggerInterface $logger
     * @param EntityManager $em
     */
    public function __construct(LoggerInterface $logger, EntityManager $em)
    {
        $this->logger = $logger;
        $this->em = $em;
    }

    /**
     * @param Evaluation $evaluation
     */
    public function getCharts(Evaluation $evaluation)
    {
        /** @var Section $section */
        foreach ($evaluation->getSections() as $section) {
            /** @var Criteria $criteria */
            foreach ($section->getCriterias() as $criteria) {
                if ($criteria->getCriteriaType()->getId() !== Constants::CRITERIA_TYPE_COMMENT) {
                    $this->setCriteriaCharts($criteria);
                }
            }
        }
    }

    /**
     * @param Criteria $criteria
     */
    private function setCriteriaCharts(Criteria $criteria)
    {
        switch ($criteria->getCriteriaType()->getId()) {
            case Constants::CRITERIA_TYPE_CHOICE:
                $this->setChartsForChoice($criteria);
                break;
            case Constants::CRITERIA_TYPE_JUDGMENT:
                $this->setChartsForJudgment($criteria);
                break;
        }
    }

    /**
     * @param Criteria $criteria
     */
    private function setChartsForChoice(Criteria $criteria)
    {
        $chart = array();
        /** @var CriteriaChoice $criteriaChoice */
        foreach ($criteria->getCriteriaChoices() as $criteriaChoice) {
            $chart[(string)$criteriaChoice->getMark()] = 0;
        }
        /** @var CorrectionCriteria $correctionCriteria */
        foreach ($criteria->getCorrectionCriterias() as $correctionCriteria) {
            $correction = $correctionCriteria->getCorrectionSection()->getCorrection();
            if ($correction->isStudentCorrection()) {
                if ($correctionCriteria->getMark() !== null) {
                    $chart[(string)$correctionCriteria->getMark()]++;
                }
            }
        }
        $criteria->setChart($chart);
    }

    /**
     * @param Criteria $criteria
     */
    private function setChartsForJudgment(Criteria $criteria)
    {
        $firstTier = round($criteria->getMarkMax() / 3, 2);
        $secondTier = round($criteria->getMarkMax() * 2 / 3, 2);
        $markMax = round($criteria->getMarkMax(), 2);
        $firstTierLabel = '[0 ; ' . $firstTier . '[';
        $secondTierLabel = '[' . $firstTier . ' ; ' . $secondTier . '[';
        $thirdTierLabel = '[' . $secondTier . ' ; ' . $markMax . ']';
        $charts = array(
            $firstTierLabel => 0,
            $secondTierLabel => 0,
            $thirdTierLabel => 0
        );
        /** @var CorrectionCriteria $correctionCriteria */
        foreach ($criteria->getCorrectionCriterias() as $correctionCriteria) {
            $correction = $correctionCriteria->getCorrectionSection()->getCorrection();
            if ($correction->isStudentCorrection()) {
                if ($correctionCriteria->getMark() !== null) {
                    if ($correctionCriteria->getMark() < $firstTier) {
                        $charts[$firstTierLabel]++;
                    } else if ($correctionCriteria->getMark() < $secondTier) {
                        $charts[$secondTierLabel]++;
                    } else {
                        $charts[$thirdTierLabel]++;
                    }
                }
            }
        }
        $criteria->setChart($charts);
    }

    /**
     * @param Trapezium $trapezium
     */
    public function setDefaultTrapezium(Trapezium $trapezium)
    {
        /** @var Criteria $criteria */
        $criteria = $trapezium->getCriteria();
        /** @var float $range */
        $range = 0;
        /** @var float $bound100 */
        $bound100 = 0;
        switch ($criteria->getCriteriaType()->getId()) {
            case Constants::CRITERIA_TYPE_CHOICE:
                $range = $this->getCriteriaChoicesMaxMark($criteria) - $this->getCriteriaChoicesMinMark($criteria);
                $bound100 = $this->getSmallestDifferenceBetweenChoices($criteria) / (float)2;
                break;
            case Constants::CRITERIA_TYPE_JUDGMENT:
                $range = $criteria->getMarkMax() - $criteria->getMarkMin();
                $bound100 = $criteria->getPrecision() / (float)2;
                break;
        }
        /** @var float $bound0 */
        $bound0 = $range / 3;
        if ($bound100 > $bound0) {
            $bound0 = ($bound100 + $range) / (float)2;
        }

        $trapezium->setMin0(-$bound0);
        $trapezium->setMax0($bound0);
        $trapezium->setMin100(-$bound100);
        $trapezium->setMax100($bound100);
    }

    /**
     * @param Criteria $criteria
     * @return float
     */
    public function getCriteriaChoicesMaxMark(Criteria $criteria)
    {
        /** @var float $maxMark */
        $maxMark = 0;
        /** @var CriteriaChoice $criteriaChoice */
        foreach ($criteria->getCriteriaChoices() as $criteriaChoice) {
            $maxMark = max($maxMark, $criteriaChoice->getMark());
        }
        return $maxMark;
    }

    public function getCriteriaChoicesMinMark(Criteria $criteria)
    {
        /** @var float $minMark */
        $minMark = 0;
        /** @var CriteriaChoice $criteriaChoice */
        foreach ($criteria->getCriteriaChoices() as $criteriaChoice) {
            $minMark = min($minMark, $criteriaChoice->getMark());
        }
        return $minMark;
    }

    /**
     * @param Criteria $criteria
     * @return float|null
     */
    private function getSmallestDifferenceBetweenChoices(Criteria $criteria)
    {
        /** @var float $temp */
        $smallestDiff = null;
        /** @var float $previousMark */
        $previousMark = 0;
        /** @var CriteriaChoice $criteriaChoice */
        foreach ($criteria->getCriteriaChoices() as $criteriaChoice) {
            if (!$smallestDiff || abs($criteriaChoice->getMark() - $previousMark) < $smallestDiff) {
                $smallestDiff = abs($criteriaChoice->getMark() - $previousMark);
            }
            $previousMark = abs($criteriaChoice->getMark());
        }
        return $smallestDiff;
    }

    public function getQualityStats(Evaluation $evaluation)
    {
        $response = array();

        /** @var CorrectionCriteriaRepository $correctionCriteriaRepository */
        $correctionCriteriaRepository = $this->em->getRepository('AppBundle:CorrectionCriteria');

        if ($evaluation->getIndividualCorrection()) {
            if ($evaluation->getIndividualAssignment()) {
                $users = $evaluation->getUsers()->toArray();
            } else {
                $users = array();
                /** @var Group $group */
                foreach ($evaluation->getGroups() as $group) {
                    /** @var User $user */
                    foreach ($group->getUsers() as $user) {
                        $users[] = $user;
                    }
                }
            }

            // Sort sections and criterias by order
            /** @var ArrayCollection $sections */
            $sections = $evaluation->getSections();
            $iterator = $sections->getIterator();
            $iterator->uasort(
                function ($sectionA, $sectionB) {
                    /** @var Section $sectionA */
                    /** @var Section $sectionB */
                    return ($sectionA->getOrder() < $sectionB->getOrder() ? -1 : 1);
                });
            $sections = new ArrayCollection(iterator_to_array($iterator));

            /** @var Section $section */
            foreach ($sections as $section) {
                /** @var ArrayCollection $criterias */
                $criterias = $section->getCriterias();
                $iterator = $criterias->getIterator();
                $iterator->uasort(
                    function ($criteriaA, $criteriaB) {
                        /** @var Criteria $criteriaA */
                        /** @var Criteria $criteriaB */
                        return ($criteriaA->getOrder() < $criteriaB->getOrder() ? -1 : 1);
                    });
                $criterias = new ArrayCollection(iterator_to_array($iterator));
                /** @var Criteria $criteria */
                foreach ($section->getCriterias() as $criteria) {
                    $section->removeCriteria($criteria);
                }
                foreach ($criterias as $criteria) {
                    $section->addCriteria($criteria);
                }
            }

            /** @var User $user */
            foreach ($users as $user) {
                $line = array();
                $line['user'] = $user;
                $criteriasReliability = array();
                $sectionId = 1;

                /** @var Section $section */
                foreach ($sections as $section) {
                    $criteriaId = 1;
                    /** @var Criteria $criteria */
                    foreach ($section->getCriterias() as $criteria) {
                        if ($criteria->getCriteriaType()->getId() == Constants::CRITERIA_TYPE_COMMENT) {
                            $criteriasReliability['S' . $sectionId . ' C' . $criteriaId] = '';
                        } else {
                            $correctionCriterias = $correctionCriteriaRepository->findByUserAndCriteria($user, $criteria);
                            $reliabilitySum = 0;
                            $numCorrections = 0;
                            /** @var CorrectionCriteria $correctionCriteria */
                            foreach ($correctionCriterias as $correctionCriteria) {
                                if ($correctionCriteria->getCorrectionSection()->getCorrection()->getDateSubmission()) {
                                    $numCorrections++;
                                    $reliabilitySum += $correctionCriteria->getReliability();
                                }
                            }
                            $criteriasReliability['S' . $sectionId . ' C' . $criteriaId] = $numCorrections > 0 ? $reliabilitySum / $numCorrections : '';
                        }
                        $criteriaId++;
                    }
                    $sectionId++;
                }
                $line['criterias_reliability'] = $criteriasReliability;
                $reliabilitySum = 0;
                $numCriterias = 0;
                foreach ($criteriasReliability as $reliability) {
                    if (is_numeric($reliability)) {
                        $reliabilitySum += $reliability;
                        $numCriterias++;
                    }
                }
                $line['average_reliability'] = $numCriterias > 0 ? $reliabilitySum / $numCriterias : '';
                $response[] = $line;
            }
        } else {
            /** @var Group $group */
            foreach ($evaluation->getGroups() as $group) {
                $line = array();
                $line['group'] = $group;
                $criteriasReliability = array();
                $sectionId = 1;
                /** @var Section $section */
                foreach ($evaluation->getSections() as $section) {
                    $criteriaId = 1;
                    /** @var Criteria $criteria */
                    foreach ($section->getCriterias() as $criteria) {
                        if ($criteria->getCriteriaType()->getId() == Constants::CRITERIA_TYPE_COMMENT) {
                            $criteriasReliability['S' . $sectionId . ' C' . $criteriaId] = '';
                        } else {
                            $correctionCriterias = $correctionCriteriaRepository->findByGroupAndCriteria($group, $criteria);
                            $reliabilitySum = 0;
                            $numCorrections = 0;
                            /** @var CorrectionCriteria $correctionCriteria */
                            foreach ($correctionCriterias as $correctionCriteria) {
                                if ($correctionCriteria->getCorrectionSection()->getCorrection()->getDateSubmission()) {
                                    $numCorrections++;
                                    $reliabilitySum += $correctionCriteria->getReliability();
                                }
                            }
                            $criteriasReliability['S' . $sectionId . ' C' . $criteriaId] = $numCorrections > 0 ? $reliabilitySum / $numCorrections : '';
                            $criteriaId++;
                        }
                    }
                    $sectionId++;
                }
                $line['criterias_reliability'] = $criteriasReliability;
                $reliabilitySum = 0;
                $numCriterias = 0;
                foreach ($criteriasReliability as $reliability) {
                    if (is_numeric($reliability)) {
                        $reliabilitySum += $reliability;
                        $numCriterias++;
                    }
                }
                $line['average_reliability'] = $numCriterias > 0 ? $reliabilitySum / $numCriterias : '';
                $response[] = $line;
            }
        }
        return $response;
    }

    /**
     * @param Assignment $assignment
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function setStats(Assignment $assignment)
    {
        // Set average mark for each criteria
        /** @var Section $section */
        foreach ($assignment->getEvaluation()->getSections() as $section) {
            /** @var Criteria $criteria */
            foreach ($section->getCriterias() as $criteria) {
                $this->setCriteriaAverageMark($criteria);
            }
        }

        // Set average mark for each assignmentCriteria
        /** @var AssignmentSection $assignmentSection */
        foreach ($assignment->getAssignmentSections() as $assignmentSection) {
            /** @var AssignmentCriteria $assignmentCriteria */
            foreach ($assignmentSection->getAssignmentCriterias() as $assignmentCriteria) {
                $this->setAssignmentCriteriaRawMark($assignmentCriteria);
            }
        }

        // Set reliability for each correctionCriteria
        /** @var Correction $correction */
        foreach ($assignment->getCorrections() as $correction) {
            if ($correction->getDateSubmission() && $correction->isStudentCorrection()) {
                /** @var CorrectionSection $correctionSection */
                foreach ($correction->getCorrectionSections() as $correctionSection) {
                    /** @var CorrectionCriteria $correctionCriteria */
                    foreach ($correctionSection->getCorrectionCriterias() as $correctionCriteria) {
                        $this->setCorrectionCriteriaReliability($correctionCriteria);
                    }
                }
            }
        }

        // Set assignmentCriteria stats
        foreach ($assignment->getAssignmentSections() as $assignmentSection) {
            foreach ($assignmentSection->getAssignmentCriterias() as $assignmentCriteria) {
                if ($assignmentCriteria->getCriteria()->getCriteriaType()->getId() !== Constants::CRITERIA_TYPE_COMMENT) {
                    $this->setAssignmentCriteriaReliability($assignmentCriteria);
                    $this->setAssignmentCriteriaStandardDeviation($assignmentCriteria);
                    $this->setAssignmentCriteriaFinalMark($assignmentCriteria);
                }
            }
        }

        // Set correction stats
        /** @var Correction $correction */
        foreach ($assignment->getCorrections() as $correction) {
            if ($correction->getDateSubmission()) {
                $this->setCorrectionStats($correction);
            }
        }

        // Set assignment stats
        $numMarks = 0;
        $markSum = 0;
        $weightedMarkSum = 0;
        $reliabilitySum = 0;
        /** @var Correction $correction */
        foreach ($assignment->getCorrections() as $correction) {
            if ($correction->getDateSubmission()) {
                if ($correction->isStudentCorrection()) {
                    if ($correction->getMark() !== null) {
                        $numMarks++;
                        $markSum += $correction->getMark();
                        $weightedMarkSum += $correction->getMark() * $correction->getReliability();
                        $reliabilitySum += $correction->getReliability();
                    }
                }
            }
        }
        if ($numMarks != 0) {
            $assignment->setRawMark($markSum / (float)$numMarks);
            $assignment->setReliability($reliabilitySum / (float)$numMarks);
        }
        if ($reliabilitySum != 0) {
            $assignment->setWeightedMark($weightedMarkSum / $reliabilitySum);
        } else {
            $assignment->setWeightedMark(null);
        }

        $squareSum = 0;
        /** @var Correction $correction */
        foreach ($assignment->getCorrections() as $correction) {
            if ($correction->isStudentCorrection()) {
                if ($correction->getMark() !== null) {
                    $squareSum += ($correction->getMark() - $assignment->getRawMark()) ** 2;
                }
            }
        }
        if ($numMarks != 0) {
            $assignment->setStandardDeviation(sqrt($squareSum / (float)$numMarks));
        }

        $mark = $assignment->getWeightedMark();

        /** @var Evaluation $evaluation */
        $evaluation = $assignment->getEvaluation();

        switch ($evaluation->getMarkMode()->getId()) {
            case Constants::EVALUATION_MARK_MODE_AVERAGE:
                $mark = $assignment->getRawMark();
                break;
            case Constants::EVALUATION_MARK_MODE_WEIGHTED_AVERAGE:
                $mark = $assignment->getWeightedMark();
                if ($mark === null) {
                    $mark = $assignment->getRawMark();
                }
                break;
        }

        if ($evaluation->getUseTeacherMark()) {
            /** @var Correction $correction */
            foreach ($assignment->getCorrections() as $correction) {
                if ($correction->getUser()) {
                    if ($correction->getUser()->getRole()->getId() == Constants::ROLE_TEACHER) {
                        if ($correction->getMark() !== null) {
                            $mark = $correction->getMark();
                            break;
                        }
                    }
                }
            }
        }

        $assignment->setMark(
            $this->roundMark(
                $mark,
                $evaluation->getMarkPrecisionMode()->getId(),
                $evaluation->getMarkRoundMode()->getId()
            )
        );

        $this->setEvaluationAverageMark($evaluation);

        $this->em->flush();
    }

    private function setCriteriaAverageMark(Criteria $criteria)
    {
        if ($criteria->getCriteriaType()->getId() !== Constants::CRITERIA_TYPE_COMMENT) {
            $markSum = 0;
            $numMarks = 0;
            /** @var CorrectionCriteria $correctionCriteria */
            foreach ($criteria->getCorrectionCriterias() as $correctionCriteria) {
                if ($correctionCriteria->getMark() !== null) {
                    $markSum += $correctionCriteria->getMark();
                    $numMarks++;
                }
            }
            if ($markSum > 0) {
                $criteria->setAverageMark($markSum / (float)$numMarks);
            }
        }
    }

    private function setAssignmentCriteriaRawMark(AssignmentCriteria $assignmentCriteria)
    {
        $numMarks = 0;
        $markSum = 0;
        /** @var CorrectionCriteria $correctionCriteria */
        foreach ($assignmentCriteria->getCriteria()->getCorrectionCriterias() as $correctionCriteria) {
            /** @var Correction $correction */
            $correction = $correctionCriteria->getCorrectionSection()->getCorrection();
            if ($correction->getAssignment() === $assignmentCriteria->getAssignmentSection()->getAssignment()) {
                if ($correction->getDateSubmission()) {
                    if ($correction->isStudentCorrection()) {
                        if ($correctionCriteria->getMark() !== null) {
                            $numMarks++;
                            $markSum += $correctionCriteria->getMark();
                        }
                    }
                }
            }
        }
        if ($numMarks != 0) {
            $assignmentCriteria->setRawMark($markSum / (float)$numMarks);
        }
    }

    private function setCorrectionCriteriaReliability(CorrectionCriteria $correctionCriteria)
    {
        $averageMark = $this->getAssignmentCriteriaMark($correctionCriteria);
        $teacherMark = $this->getCriteriaTeacherMark($correctionCriteria);
        $correctionCriteria->setReliability($this->getReliability($correctionCriteria, $averageMark));
        $correctionCriteria->setRecalculatedReliability($this->getReliability($correctionCriteria, $teacherMark));
    }

    /**
     * @param CorrectionCriteria $correctionCriteria
     * @return float|null
     */
    public function getAssignmentCriteriaMark(CorrectionCriteria $correctionCriteria)
    {
        /** @var Assignment $assignment */
        $assignment = $correctionCriteria->getCorrectionSection()->getCorrection()->getAssignment();
        /** @var AssignmentCriteria $assignmentCriteria */
        foreach ($correctionCriteria->getCriteria()->getAssignmentCriterias() as $assignmentCriteria) {
            if ($assignmentCriteria->getAssignmentSection()->getAssignment() === $assignment) {
                return $assignmentCriteria->getRawMark();
            }
        }
        return null;
    }

    /**
     * @param CorrectionCriteria $correctionCriteria
     * @return float|null
     */
    private function getCriteriaTeacherMark(CorrectionCriteria $correctionCriteria)
    {
        /** @var Assignment $assignment */
        $assignment = $correctionCriteria->getCorrectionSection()->getCorrection()->getAssignment();
        /** @var Correction $correction */
        foreach ($assignment->getCorrections() as $correction) {
            if ($correction->getUser()) {
                if ($correction->getUser()->getRole()->getId() == Constants::ROLE_TEACHER) {
                    /** @var CorrectionSection $correctionSection */
                    foreach ($correction->getCorrectionSections() as $correctionSection) {
                        /** @var CorrectionCriteria $teacherCorrectionCriteria */
                        foreach ($correctionSection->getCorrectionCriterias() as $teacherCorrectionCriteria) {
                            if ($teacherCorrectionCriteria->getCriteria() === $correctionCriteria->getCriteria()) {
                                return $teacherCorrectionCriteria->getMark();
                            }
                        }
                    }
                }
            }
        }
        return null;
    }

    /**
     * @param CorrectionCriteria $correctionCriteria
     * @param float $referenceMark
     * @return float
     */
    private function getReliability(CorrectionCriteria $correctionCriteria, $referenceMark)
    {
        if ($referenceMark === null) {
            return null;
        }
        /** @var Trapezium $trapezium */
        $trapezium = $correctionCriteria->getCriteria()->getTrapezium();
        $diff = $correctionCriteria->getMark() - $referenceMark;
        if ($diff >= $trapezium->getMin100() && $diff <= $trapezium->getMax100()) {
            return (float)100;
        }
        if ($diff <= $trapezium->getMin0() || $diff >= $trapezium->getMax0()) {
            return (float)0;
        }
        if ($diff < $trapezium->getMin100()) {
            return ($trapezium->getMin0() - $diff) * 100 / ($trapezium->getMin0() - $trapezium->getMin100());
        } else {
            return ($trapezium->getMax0() - $diff) * 100 / ($trapezium->getMax0() - $trapezium->getMax100());
        }
    }

    private function setAssignmentCriteriaReliability(AssignmentCriteria $assignmentCriteria)
    {
        $numMarks = 0;
        $weightedMarkSum = 0;
        $reliabilitySum = 0;
        /** @var CorrectionCriteria $correctionCriteria */
        foreach ($assignmentCriteria->getCriteria()->getCorrectionCriterias() as $correctionCriteria) {
            /** @var Correction $correction */
            $correction = $correctionCriteria->getCorrectionSection()->getCorrection();
            if ($correction->getAssignment() === $assignmentCriteria->getAssignmentSection()->getAssignment()) {
                if ($correction->getDateSubmission()) {
                    if ($correction->isStudentCorrection()) {
                        if ($correctionCriteria->getMark() !== null) {
                            $numMarks++;
                            $weightedMarkSum += $correctionCriteria->getMark() * $correctionCriteria->getReliability();
                            $reliabilitySum += $correctionCriteria->getReliability();
                        }
                    }
                }
            }
        }
        if ($numMarks != 0) {
            $assignmentCriteria->setReliability($reliabilitySum / (float)$numMarks);
        }
        if ($reliabilitySum != 0) {
            $assignmentCriteria->setWeightedMark($weightedMarkSum / $reliabilitySum);
        } else {
            $assignmentCriteria->setWeightedMark(null);
        }
    }

    private function setAssignmentCriteriaStandardDeviation(AssignmentCriteria $assignmentCriteria)
    {
        $squareSum = 0;
        $numMarks = 0;
        /** @var CorrectionCriteria $correctionCriteria */
        foreach ($assignmentCriteria->getCriteria()->getCorrectionCriterias() as $correctionCriteria) {
            /** @var Correction $correction */
            $correction = $correctionCriteria->getCorrectionSection()->getCorrection();
            if ($correction->getAssignment() === $assignmentCriteria->getAssignmentSection()->getAssignment()) {
                if ($correction->getDateSubmission()) {
                    if ($correction->isStudentCorrection()) {
                        if ($correctionCriteria->getMark() !== null) {
                            $numMarks++;
                            $squareSum += ($correctionCriteria->getMark() - $assignmentCriteria->getRawMark()) ** 2;
                        }
                    }
                }
            }
        }
        if ($numMarks != 0) {
            $assignmentCriteria->setStandardDeviation(sqrt($squareSum / (float)$numMarks));
        }
    }

    private function setAssignmentCriteriaFinalMark(AssignmentCriteria $assignmentCriteria)
    {
        $mark = $assignmentCriteria->getWeightedMark();

        /** @var Evaluation $evaluation */
        $evaluation = $assignmentCriteria->getAssignmentSection()->getAssignment()->getEvaluation();

        switch ($evaluation->getMarkMode()->getId()) {
            case Constants::EVALUATION_MARK_MODE_AVERAGE:
                $mark = $assignmentCriteria->getRawMark();
                break;
            case Constants::EVALUATION_MARK_MODE_WEIGHTED_AVERAGE:
                $mark = $assignmentCriteria->getWeightedMark() === null ?
                    $assignmentCriteria->getRawMark() : $assignmentCriteria->getWeightedMark();
                break;
        }

        if ($evaluation->getUseTeacherMark()) {
            /** @var Correction $correction */
            foreach ($assignmentCriteria->getAssignmentSection()->getAssignment()->getCorrections() as $correction) {
                if ($correction->getUser()) {
                    if ($correction->getUser()->getRole()->getId() == Constants::ROLE_TEACHER) {
                        if ($correction->getMark() !== null) {
                            $mark = $correction->getMark();
                            break;
                        }
                    }
                }
            }
        }

        $assignmentCriteria->setMark(
            $this->roundMark(
                $mark,
                $evaluation->getMarkPrecisionMode()->getId(),
                $evaluation->getMarkRoundMode()->getId()
            )
        );
    }

    /**
     * @param float $mark
     * @param int $precisionMode
     * @param int $roundMode
     * @return float|null
     */
    private function roundMark($mark, $precisionMode, $roundMode)
    {
        if ($mark === null) {
            return null;
        }
        $factor = 100;
        switch ($precisionMode) {
            case Constants::EVALUATION_MARK_PRECISION_MODE_POINT:
                $factor = 1;
                break;
            case Constants::EVALUATION_MARK_PRECISION_MODE_HALF_POINT:
                $factor = 2;
                break;
            case Constants::EVALUATION_MARK_PRECISION_MODE_ONE_DECIMAL:
                $factor = 10;
                break;
            case Constants::EVALUATION_MARK_PRECISION_MODE_TWO_DECIMAL:
                $factor = 100;
                break;
        }
        switch ($roundMode) {
            case Constants::EVALUATION_MARK_ROUND_MODE_NEAR:
                return round($mark * $factor) / (float)$factor;
                break;
            case Constants::EVALUATION_MARK_ROUND_MODE_ABOVE:
                return ceil($mark * $factor) / (float)$factor;
                break;
            case Constants::EVALUATION_MARK_ROUND_MODE_BELOW:
                return floor($mark * $factor) / (float)$factor;
                break;
        }
        return null;
    }

    /**
     * @param Correction $correction
     */
    private function setCorrectionStats(Correction $correction)
    {
        $markSum = 0;
        $maxMark = 0;
        $reliabilitySum = 0;
        $recalculatedReliabilitySum = null;
        $numMarks = 0;
        /** @var CorrectionSection $correctionSection */
        foreach ($correction->getCorrectionSections() as $correctionSection) {
            /** @var CorrectionCriteria $correctionCriteria */
            foreach ($correctionSection->getCorrectionCriterias() as $correctionCriteria) {
                /** @var Criteria $criteria */
                $criteria = $correctionCriteria->getCriteria();
                if ($criteria->getCriteriaType()->getId() != Constants::CRITERIA_TYPE_COMMENT) {
                    if ($correctionCriteria->getMark() !== null) {
                        $markSum += $correctionCriteria->getMark() * $criteria->getWeight();
                        $reliabilitySum += $correctionCriteria->getReliability();
                        if ($correctionCriteria->getRecalculatedReliability() !== null) {
                            $recalculatedReliabilitySum += $correctionCriteria->getRecalculatedReliability();
                        }
                        $numMarks++;
                        switch ($criteria->getCriteriaType()->getId()) {
                            case Constants::CRITERIA_TYPE_CHOICE:
                                $maxChoice = 0;
                                /** @var CriteriaChoice $criteriaChoice */
                                foreach ($criteria->getCriteriaChoices() as $criteriaChoice) {
                                    $maxChoice = max($criteriaChoice->getMark(), $maxChoice);
                                }
                                $maxMark += $maxChoice * $criteria->getWeight();
                                break;
                            case Constants::CRITERIA_TYPE_JUDGMENT:
                                $maxMark += $criteria->getMarkMax() * $criteria->getWeight();
                                break;
                        }
                    }
                }
            }
        }
        if ($numMarks != 0) {
            $mark = ($markSum * 20) / $maxMark;
            $correction->setMark($mark);
            if ($correction->isStudentCorrection()) {
                $correction->setReliability($reliabilitySum / $numMarks);
                if ($recalculatedReliabilitySum !== null) {
                    $correction->setRecalculatedReliability($recalculatedReliabilitySum / $numMarks);
                }
            }
        }
    }

    /**
     * @param Evaluation $evaluation
     */
    private function setEvaluationAverageMark(Evaluation $evaluation)
    {
        $markSum = 0;
        $numMarks = 0;
        /** @var Assignment $assignment */
        foreach ($evaluation->getAssignments() as $assignment) {
            if ($assignment->getMark() !== null) {
                $numMarks++;
                $markSum += $assignment->getMark();
            }
        }
        if ($numMarks !== 0) {
            $evaluation->setAssignmentAverage(round($markSum / $numMarks, 2));
        }
    }
}
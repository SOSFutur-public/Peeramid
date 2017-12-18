<?php

namespace AppBundle\Entity;

use AppBundle\Constants;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * CorrectionCriteria
 *
 * @ORM\Table(name="correction_criterias")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CorrectionCriteriaRepository")
 */
class CorrectionCriteria
{
    /**
     * @var int
     * @Groups({"id"})
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var float
     * @Groups({"correction-edit", "evaluation-stats-criterias", "assignment-corrections"})
     *
     * @ORM\Column(name="mark", type="float", nullable=true)
     */
    private $mark;

    /**
     * @var string
     * @Groups({"correction-edit", "assignment-corrections"})
     *
     * @ORM\Column(name="comments", type="text", nullable=true)
     */
    private $comments;

    /**
     * @var float
     * @Groups({"evaluation-stats-criterias"})
     *
     * @ORM\Column(name="reliability", type="float", nullable=true)
     */
    private $reliability;

    /**
     * @var float
     * @Groups({"evaluation-stats-criterias"})
     *
     * @ORM\Column(name="recalculated_reliability", type="float", nullable=true)
     */
    private $recalculatedReliability;

    /**
     * @var \AppBundle\Entity\Criteria
     * @Groups({"correction-edit", "evaluation-stats-criterias", "assignment-corrections"})
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Criteria", inversedBy="correction_criterias")
     */
    private $criteria;

    /**
     * @var \AppBundle\Entity\CorrectionSection
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CorrectionSection", inversedBy="correction_criterias")
     */
    private $correction_section;

    /**
     * @var \AppBundle\Entity\CorrectionOpinion
     * @Groups({"correction-opinion-edit", "assignment-corrections", "evaluation-stats-criterias"})
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\CorrectionOpinion", mappedBy="correction_criteria", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $correction_opinion;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get mark
     *
     * @return float
     */
    public function getMark()
    {
        return $this->mark;
    }

    /**
     * Set mark
     *
     * @param float $mark
     *
     * @return CorrectionCriteria
     */
    public function setMark($mark)
    {
        $this->mark = $mark;

        return $this;
    }

    /**
     * Get comments
     *
     * @return string
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Set comments
     *
     * @param string $comments
     *
     * @return CorrectionCriteria
     */
    public function setComments($comments)
    {
        $this->comments = $comments;

        return $this;
    }

    /**
     * @return CorrectionOpinion
     */
    public function getCorrectionOpinion()
    {
        return $this->correction_opinion;
    }

    /**
     * @param CorrectionOpinion $correction_opinion
     * @return CorrectionCriteria
     */
    public function setCorrectionOpinion($correction_opinion)
    {
        $this->correction_opinion = $correction_opinion;
        $correction_opinion->setCorrectionCriteria($this);

        return $this;
    }

    /**
     * @return CorrectionSection
     */
    public function getCorrectionSection()
    {
        return $this->correction_section;
    }

    /**
     * @param $correctionSection
     */
    public function setCorrectionSection($correctionSection)
    {
        $this->correction_section = $correctionSection;
    }

    /**
     * Get reliability
     *
     * @return float
     */
    public function getReliability()
    {
        return $this->reliability;
    }

    /**
     * Set reliability
     *
     * @param float $reliability
     *
     * @return CorrectionCriteria
     */
    public function setReliability($reliability)
    {
        $this->reliability = $reliability;

        return $this;
    }

    /**
     * Get recalculatedReliability
     *
     * @return float
     */
    public function getRecalculatedReliability()
    {
        return $this->recalculatedReliability;
    }

    /**
     * Set recalculatedReliability
     *
     * @param float $recalculatedReliability
     *
     * @return CorrectionCriteria
     */
    public function setRecalculatedReliability($recalculatedReliability)
    {
        $this->recalculatedReliability = $recalculatedReliability;

        return $this;
    }

    /**
     * @param ExecutionContextInterface $context
     *
     * @Assert\Callback()
     */
    public function checkWithCriteriaType(ExecutionContextInterface $context)
    {
        switch ($this->criteria->getCriteriaType()->getId()) {
            case Constants::CRITERIA_TYPE_JUDGMENT:
                if (isset($this->comments)) {
                    if (!isset($this->mark)) {
                        $context
                            ->buildViolation('not_null')
                            ->atPath('mark')
                            ->setInvalidValue($this->mark)
                            ->addViolation();
                    }
                }
                if ($this->mark > $this->criteria->getMarkMax()) {
                    $context
                        ->buildViolation('lower_than_var')
                        ->setParameter('%var%', Constants::VARS['mark_max'])
                        ->setParameter('%value%', $this->criteria->getMarkMax())
                        ->atPath('mark')
                        ->setInvalidValue($this->mark)
                        ->addViolation();
                }
                if ($this->mark < $this->criteria->getMarkMin()) {
                    $context
                        ->buildViolation('greater_than_var')
                        ->setParameter('%var%', Constants::VARS['mark_min'])
                        ->setParameter('%value%', $this->criteria->getMarkMin())
                        ->atPath('mark')
                        ->setInvalidValue($this->mark)
                        ->addViolation();
                }
                if ($this->mark !== null) {
                    if (fmod($this->mark * 10, $this->criteria->getPrecision() * 10) !== (float)0) {
                        $context
                            ->buildViolation('multiple_of_var')
                            ->setParameter('%var%', Constants::VARS['precision'])
                            ->setParameter('%value%', $this->criteria->getPrecision())
                            ->atPath('mark')
                            ->setInvalidValue($this->mark)
                            ->addViolation();
                    }
                }
                if ($this->correction_section->getCorrection()->getDateSubmission()) {
                    if ($this->mark === null) {
                        $context
                            ->buildViolation('not_null')
                            ->atPath('mark')
                            ->setInvalidValue($this->mark)
                            ->addViolation();
                    }
                }
                break;
            case Constants::CRITERIA_TYPE_CHOICE:
                $choice = false;
                /** @var CriteriaChoice $criteriaChoice */
                foreach ($this->criteria->getCriteriaChoices() as $criteriaChoice) {
                    if ($this->mark == $criteriaChoice->getMark()) {
                        $choice = true;
                        break;
                    }
                }
                if (!$choice) {
                    $context
                        ->buildViolation('must_be_choice')
                        ->atPath('mark')
                        ->setInvalidValue($this->mark)
                        ->addViolation();
                }
                if ($this->correction_section->getCorrection()->getDateSubmission()) {
                    if ($this->mark === null) {
                        $context
                            ->buildViolation('not_null')
                            ->atPath('mark')
                            ->setInvalidValue($this->mark)
                            ->addViolation();
                    }
                }
                break;
            case Constants::CRITERIA_TYPE_COMMENT:
                if ($this->correction_section->getCorrection()->getDateSubmission()) {
                    if ($this->comments === null || trim($this->comments) === '') {
                        $context
                            ->buildViolation('not_null')
                            ->atPath('comments')
                            ->setInvalidValue($this->comments)
                            ->addViolation();
                    }
                }
                break;
        }
    }

    /**
     * @return Criteria
     */
    public function getCriteria()
    {
        return $this->criteria;
    }

    /**
     * @param Criteria $criteria
     */
    public function setCriteria($criteria)
    {
        $this->criteria = $criteria;
    }
}

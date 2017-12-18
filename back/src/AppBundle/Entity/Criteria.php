<?php

namespace AppBundle\Entity;

use AppBundle\Constants;
use AppBundle\Validator\CollectionSameItem;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Criteria
 *
 * @ORM\Table(name="criterias")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CriteriaRepository")
 *
 * @CollectionSameItem(collection="criteria_choices", variable="name")
 */
class Criteria
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
     * @var string
     * @Groups({"evaluation-edit", "correction-edit", "criteria-charts", "assignment-corrections"})
     *
     * @ORM\Column(name="description", type="text")
     * @Assert\NotBlank(message="not_blank")
     */
    private $description;

    /**
     * @var int
     * @Groups({"evaluation-edit", "correction-edit", "criteria-charts", "assignment-corrections",
     *     "evaluation-stats"})
     *
     * @ORM\Column(name="`order`", type="integer")
     * @Assert\NotBlank(message="not_blank")
     * @Assert\GreaterThan(0, message="greater_than_0")\
     */
    private $order;

    /**
     * @var int
     * @Groups({"evaluation-edit", "criteria-charts"})
     *
     * @ORM\Column(name="weight", type="integer")
     * @Assert\NotBlank(message="not_blank")
     * @Assert\GreaterThan(-1, message="greater_than_0")\
     */
    private $weight;

    /**
     * @var bool
     * @Groups({"evaluation-edit", "assignment-feedback", "assignment-corrections"})
     *
     * @ORM\Column(name="show_mark", type="boolean")
     * @Assert\NotNull(message="not_null")
     */
    private $showMark = true;

    /**
     * @var bool
     * @Groups({"evaluation-edit", "assignment-feedback", "assignment-corrections"})
     *
     * @ORM\Column(name="show_teacher_comments", type="boolean")
     * @Assert\NotNull(message="not_null")
     */
    private $showTeacherComments = true;

    /**
     * @var bool
     * @Groups({"evaluation-edit", "assignment-feedback", "assignment-corrections"})
     *
     * @ORM\Column(name="show_students_comments", type="boolean")
     * @Assert\NotNull(message="not_null")
     */
    private $showStudentsComments = true;

    /**
     * @var float
     * @Groups({"correction-edit", "evaluation-edit", "assignment-corrections"})
     *
     * @ORM\Column(name="mark_max", type="float", nullable=true)
     */
    private $markMax;

    /**
     * @var float
     * @Groups({"correction-edit", "evaluation-edit", "assignment-corrections"})
     *
     * @ORM\Column(name="mark_min", type="float", nullable=true)
     */
    private $markMin;

    /**
     * @var float
     * @Groups({"correction-edit", "evaluation-edit", "assignment-corrections"})
     *
     * @ORM\Column(name="`precision`", type="float", nullable=true)
     */
    private $precision;

    /**
     * @var float
     * @ORM\Column(name="average_mark", type="float", nullable=true)
     */
    private $averageMark;

    /**
     * @var \AppBundle\Entity\CriteriaType
     * @Groups({"evaluation-edit", "correction-edit", "criteria-charts", "assignment-corrections"})
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CriteriaType")
     * @Assert\NotNull(message="not_null")
     */
    private $criteriaType;

    /**
     * @var \AppBundle\Entity\Section
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Section", inversedBy="criterias", cascade={"persist"})
     */
    private $section;

    /**
     * @var \AppBundle\Entity\Trapezium
     * @Groups({"trapezium-edit"})
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Trapezium", mappedBy="criteria", cascade={"persist", "remove"}, orphanRemoval=true)
     * @Assert\Valid()
     */
    private $trapezium;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\CorrectionCriteria", mappedBy="criteria", orphanRemoval=true)
     */
    private $correction_criterias;

    /**
     * @var ArrayCollection
     * @Groups({"evaluation-edit", "correction-edit", "assignment-corrections"})
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\CriteriaChoice", mappedBy="criteria", cascade={"persist", "remove"}, orphanRemoval=true)
     * @Assert\Valid()
     */
    private $criteria_choices;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\AssignmentCriteria", mappedBy="criteria", orphanRemoval=true)
     */
    private $assignment_criterias;

    /**
     * @var array
     * @Groups({"criteria-charts"})
     */
    private $chart;

    /**
     * @var array
     * @Groups({"trapezium-edit"})
     */
    private $differences;

    /**
     * @var float
     * @Groups({"trapezium-edit"})
     */
    private $maxDiff;

    /**
     * Criteria constructor.
     */
    public function __construct()
    {
        $this->correction_criterias = new ArrayCollection();
        $this->criteria_choices = new ArrayCollection();
        $this->assignment_criterias = new ArrayCollection();
    }

    public function __clone()
    {
        $this->id = null;
        $this->correction_criterias = new ArrayCollection();
        if ($this->trapezium) {
            $this->trapezium = clone $this->trapezium;
            $this->trapezium->setCriteria($this);
        }
        $choicesClone = new ArrayCollection();
        /** @var CriteriaChoice $criteria_choice */
        foreach ($this->criteria_choices as $criteria_choice) {
            $choiceClone = clone $criteria_choice;
            $choiceClone->setCriteria($this);
            $choicesClone->add($choiceClone);
        }
        $this->criteria_choices = $choicesClone;
    }


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
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Criteria
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get order
     *
     * @return int
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set order
     *
     * @param integer $order
     *
     * @return Criteria
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Get weight
     *
     * @return int
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * Set weight
     *
     * @param integer $weight
     *
     * @return Criteria
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;

        return $this;
    }

    /**
     * Get showMark
     *
     * @return bool
     */
    public function getShowMark()
    {
        return $this->showMark;
    }

    /**
     * Set showMark
     *
     * @param boolean $showMark
     *
     * @return Criteria
     */
    public function setShowMark($showMark)
    {
        $this->showMark = $showMark;

        return $this;
    }

    /**
     * Get showTeacherComments
     *
     * @return bool
     */
    public function getShowTeacherComments()
    {
        return $this->showTeacherComments;
    }

    /**
     * Set showTeacherComments
     *
     * @param boolean $showTeacherComments
     *
     * @return Criteria
     */
    public function setShowTeacherComments($showTeacherComments)
    {
        $this->showTeacherComments = $showTeacherComments;

        return $this;
    }

    /**
     * Get showStudentsComments
     *
     * @return bool
     */
    public function getShowStudentsComments()
    {
        return $this->showStudentsComments;
    }

    /**
     * Set showStudentsComments
     *
     * @param boolean $showStudentsComments
     *
     * @return Criteria
     */
    public function setShowStudentsComments($showStudentsComments)
    {
        $this->showStudentsComments = $showStudentsComments;

        return $this;
    }

    /**
     * @return CriteriaType
     */
    public function getCriteriaType()
    {
        return $this->criteriaType;
    }

    /**
     * @param CriteriaType $criteriaType
     */
    public function setCriteriaType($criteriaType)
    {
        $this->criteriaType = $criteriaType;
    }

    /**
     * @return Section
     */
    public function getSection()
    {
        return $this->section;
    }

    /**
     * @param Section $section
     */
    public function setSection($section)
    {
        $this->section = $section;
    }

    /**
     * @return Trapezium
     */
    public function getTrapezium()
    {
        return $this->trapezium;
    }

    /**
     * @param Trapezium $trapezium
     */
    public function setTrapezium($trapezium)
    {
        $this->trapezium = $trapezium;
        $trapezium->setCriteria($this);
    }

    /**
     * @return ArrayCollection
     */
    public function getCorrectionCriterias()
    {
        return $this->correction_criterias;
    }

    /**
     * @param CorrectionCriteria $correctionCriteria
     */
    public function addCorrectionCriteria(CorrectionCriteria $correctionCriteria)
    {
        $this->correction_criterias[] = $correctionCriteria;
        $correctionCriteria->setCriteria($this);
    }

    /**
     * @param CorrectionCriteria $correctionCriteria
     */
    public function removeCorrectionCriteria(CorrectionCriteria $correctionCriteria)
    {
        $this->correction_criterias->removeElement($correctionCriteria);
    }

    /**
     * @return ArrayCollection
     */
    public function getCriteriaChoices()
    {
        return $this->criteria_choices;
    }

    /**
     * @param CriteriaChoice $criteriaChoice
     */
    public function addCriteriaChoice(CriteriaChoice $criteriaChoice)
    {
        $this->criteria_choices[] = $criteriaChoice;
        $criteriaChoice->setCriteria($this);
    }

    public function removeCriteriaChoice(CriteriaChoice $criteriaChoice)
    {
        $this->criteria_choices->removeElement($criteriaChoice);
    }

    /**
     * @return float
     */
    public function getPrecision()
    {
        return $this->precision;
    }

    /**
     * @param float $precision
     */
    public function setPrecision($precision)
    {
        $this->precision = $precision;
    }

    /**
     * @return float
     */
    public function getMarkMax()
    {
        return $this->markMax;
    }

    /**
     * @param float $markMax
     */
    public function setMarkMax($markMax)
    {
        $this->markMax = $markMax;
    }

    /**
     * @param ExecutionContextInterface $context
     *
     * @Assert\Callback
     */
    public function checkTypeOptions(ExecutionContextInterface $context)
    {
        if ($this->criteriaType) {
            switch ($this->criteriaType->getId()) {
                case Constants::CRITERIA_TYPE_COMMENT:
                    break;
                case Constants::CRITERIA_TYPE_CHOICE:
                    if (count($this->criteria_choices) < 1) {
                        $context
                            ->buildViolation('not_blank')
                            ->atPath('criteria_choices')
                            ->setInvalidValue(array())
                            ->addViolation();
                    }
                    break;
                case Constants::CRITERIA_TYPE_JUDGMENT:
                    if ($this->markMax === null) {
                        $context
                            ->buildViolation('not_null')
                            ->atPath('mark_max')
                            ->setInvalidValue(null)
                            ->addViolation();
                    }
                    if ($this->markMin === null) {
                        $context
                            ->buildViolation('not_null')
                            ->atPath('mark_min')
                            ->setInvalidValue(null)
                            ->addViolation();
                    }
                    if ($this->markMax <= $this->markMin) {
                        $context
                            ->buildViolation('greater_than_var')
                            ->setParameter('%var%', Constants::VARS['mark_min'])
                            ->setParameter('%value%', $this->markMin)
                            ->atPath('mark_max')
                            ->setInvalidValue(null)
                            ->addViolation();
                    }
                    if (!$this->precision) {
                        $context
                            ->buildViolation('not_null')
                            ->atPath('precision')
                            ->setInvalidValue(null)
                            ->addViolation();
                    }
                    break;
            }
        }
    }

    /**
     * @return array
     */
    public function getChart()
    {
        return $this->chart;
    }

    /**
     * @param array $chart
     */
    public function setChart($chart)
    {
        $this->chart = $chart;
    }

    /**
     * Get averageMark
     *
     * @return float
     */
    public function getAverageMark()
    {
        return $this->averageMark;
    }

    /**
     * Set averageMark
     *
     * @param float $averageMark
     *
     * @return Criteria
     */
    public function setAverageMark($averageMark)
    {
        $this->averageMark = $averageMark;

        return $this;
    }

    /**
     * @return array
     */
    public function getDifferences()
    {
        return $this->differences;
    }

    /**
     * @param array $differences
     */
    public function setDifferences($differences)
    {
        $this->differences = $differences;
    }

    /**
     * Add assignmentCriteria
     *
     * @param AssignmentCriteria $assignmentCriteria
     *
     * @return Criteria
     */
    public function addAssignmentCriteria(AssignmentCriteria $assignmentCriteria)
    {
        $this->assignment_criterias[] = $assignmentCriteria;

        return $this;
    }

    /**
     * Remove assignmentCriteria
     *
     * @param AssignmentCriteria $assignmentCriteria
     */
    public function removeAssignmentCriteria(AssignmentCriteria $assignmentCriteria)
    {
        $this->assignment_criterias->removeElement($assignmentCriteria);
    }

    /**
     * Get assignmentCriterias
     *
     * @return ArrayCollection
     */
    public function getAssignmentCriterias()
    {
        return $this->assignment_criterias;
    }

    /**
     * Get markMin
     *
     * @return float
     */
    public function getMarkMin()
    {
        return $this->markMin;
    }

    /**
     * Set markMin
     *
     * @param float $markMin
     *
     * @return Criteria
     */
    public function setMarkMin($markMin)
    {
        $this->markMin = $markMin;

        return $this;
    }

    /**
     * @return float
     */
    public function getMaxDiff()
    {
        return $this->maxDiff;
    }

    /**
     * @param float $maxDiff
     */
    public function setMaxDiff(float $maxDiff)
    {
        $this->maxDiff = $maxDiff;
    }


}

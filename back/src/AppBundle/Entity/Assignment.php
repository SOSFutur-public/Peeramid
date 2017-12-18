<?php

namespace AppBundle\Entity;

use AppBundle\Constants;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Assignment
 *
 * @ORM\Table(name="assignments")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\AssignmentRepository")
 */
class Assignment
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
     * @var \DateTime
     * @Groups({"assignment-edit", "assignment-list", "correction-edit", "evaluation-stats", "assignment-corrections"})
     *
     * @ORM\Column(name="date_submission", type="datetime", nullable=true)
     */
    private $dateSubmission;

    /**
     * @var bool
     * @Groups({"assignment-edit", "assignment-list"})
     *
     * @ORM\Column(name="draft", type="boolean")
     */
    private $draft = true;

    /**
     * @var float
     * @Groups({"evaluation-stats"})
     *
     * @ORM\Column(name="raw_mark", type="float", nullable=true)
     */
    private $rawMark;

    /**
     * @var float
     * @Groups({"evaluation-stats"})
     *
     * @ORM\Column(name="standard_deviation", type="float", nullable=true)
     */
    private $standardDeviation;

    /**
     * @var float
     * @Groups({"evaluation-stats"})
     *
     * @ORM\Column(name="weighted_mark", type="float", nullable=true)
     */
    private $weightedMark;

    /**
     * @var float
     * @Groups({"evaluation-stats"})
     *
     * @ORM\Column(name="reliability", type="float", nullable=true)
     */
    private $reliability;

    /**
     * @var float
     * @Groups({"assignment-list", "evaluation-stats", "assignment-correction-list"})
     *
     * @ORM\Column(name="mark", type="float", nullable=true)
     */
    private $mark;

    /**
     * @var \AppBundle\Entity\User
     * @Groups({"assignment-edit", "evaluation-attribution", "not-anonymous-correction",
     *     "evaluation-stats", "assignment-corrections", "teacher-correction", "correction-list"})
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     */
    private $user;

    /**
     * @var \AppBundle\Entity\Group
     * @Groups({"assignment-edit", "evaluation-attribution", "evaluation-stats", "assignment-corrections",
     *     "not-anonymous-correction", "teacher-correction", "correction-list"})
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Group")
     */
    private $group;

    /**
     * @var \AppBundle\Entity\Evaluation
     * @Groups({"assignment-edit", "assignment-list", "correction-list", "correction-edit", "assignment-corrections", "assignment-correction-list"})
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Evaluation", inversedBy="assignments")
     */
    private $evaluation;

    /**
     * @var ArrayCollection
     * @Groups({"evaluation-attribution", "evaluation-stats", "assignment-correction-list"})
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Correction", mappedBy="assignment", cascade={"remove"}, orphanRemoval=true)
     */
    private $corrections;

    /**
     * @var ArrayCollection
     * @Groups({"assignment-edit", "evaluation-stats-criterias"})
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\AssignmentSection", mappedBy="assignment", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $assignmentSections;

    /**
     * @var boolean
     * @Groups("evaluation-stats")
     */
    private $warning = false;

    /**
     * Assignment constructor.
     */
    public function __construct()
    {
        $this->assignmentSections = new ArrayCollection();
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
     * @return \DateTime
     */
    public function getDateSubmission()
    {
        return $this->dateSubmission;
    }

    /**
     * @param \DateTime $dateSubmission
     */
    public function setDateSubmission(\DateTime $dateSubmission)
    {
        $this->dateSubmission = $dateSubmission;
    }

    /**
     * @return float
     */
    public function getMark()
    {
        return $this->mark;
    }

    /**
     * @param float $mark
     */
    public function setMark($mark)
    {
        $this->mark = $mark;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;
    }

    /**
     * @return Group
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param Group $group
     */
    public function setGroup(Group $group = null)
    {
        $this->group = $group;
    }

    /**
     * @return Evaluation
     */
    public function getEvaluation()
    {
        return $this->evaluation;
    }

    /**
     * @param Evaluation $evaluation
     */
    public function setEvaluation(Evaluation $evaluation)
    {
        $this->evaluation = $evaluation;
    }

    /**
     * @return ArrayCollection
     */
    public function getCorrections()
    {
        return $this->corrections;
    }

    /**
     * @param Correction $correction
     */
    public function addCorrection(Correction $correction)
    {
        $this->corrections[] = $correction;
        $correction->setAssignment($this);
    }

    /**
     * @param Correction $correction
     */
    public function removeCorrection(Correction $correction)
    {
        $this->corrections->removeElement($correction);
    }

    /**
     * @return ArrayCollection
     */
    public function getAssignmentSections()
    {
        return $this->assignmentSections;
    }

    /**
     * @param AssignmentSection $assignmentSection
     */
    public function addAssignmentSection(AssignmentSection $assignmentSection)
    {
        $this->assignmentSections[] = $assignmentSection;
        $assignmentSection->setAssignment($this);
    }

    /**
     * @param AssignmentSection $assignmentSection
     */
    public function removeAssignmentSection(AssignmentSection $assignmentSection)
    {
        $this->assignmentSections->removeElement($assignmentSection);
    }

    /**
     * @return bool
     */
    public function isDraft()
    {
        return $this->draft;
    }

    /**
     * Get draft
     *
     * @return boolean
     */
    public function getDraft()
    {
        return $this->draft;
    }

    /**
     * @param bool $draft
     */
    public function setDraft($draft)
    {
        $this->draft = $draft;
    }

    /**
     * Get rawMark
     *
     * @return float
     */
    public function getRawMark()
    {
        return $this->rawMark;
    }

    /**
     * Set rawMark
     *
     * @param float $rawMark
     *
     * @return Assignment
     */
    public function setRawMark($rawMark)
    {
        $this->rawMark = $rawMark;

        return $this;
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
     * @return Assignment
     */
    public function setReliability($reliability)
    {
        $this->reliability = $reliability;

        return $this;
    }

    /**
     * Get weightedMark
     *
     * @return float
     */
    public function getWeightedMark()
    {
        return $this->weightedMark;
    }

    /**
     * Set weightedMark
     *
     * @param float $weightedMark
     *
     * @return Assignment
     */
    public function setWeightedMark($weightedMark)
    {
        $this->weightedMark = $weightedMark;

        return $this;
    }

    /**
     * Get standardDeviation
     *
     * @return float
     */
    public function getStandardDeviation()
    {
        return $this->standardDeviation;
    }

    /**
     * Set standardDeviation
     *
     * @param float $standardDeviation
     *
     * @return Assignment
     */
    public function setStandardDeviation($standardDeviation)
    {
        $this->standardDeviation = $standardDeviation;

        return $this;
    }

    /**
     * @return string
     */
    public function getAuthorName()
    {
        if ($this->evaluation->getIndividualAssignment()) {
            return $this->user->getLastName() . ' ' . $this->user->getFirstName();
        } else {
            return $this->group->getName();
        }
    }

    /**
     * @param ExecutionContextInterface $context
     *
     * @Assert\Callback
     */
    public function checkDraft(ExecutionContextInterface $context)
    {
        if ($this->dateSubmission) {
            if ($this->draft) {
                $context
                    ->buildViolation('draft')
                    ->setParameter('%var%', Constants::VARS['submitted_assignment'])
                    ->atPath('draft')
                    ->setInvalidValue($this->draft)
                    ->addViolation();
            }
        }
    }

    /**
     * @return bool
     */
    public function isWarning(): bool
    {
        return $this->warning;
    }

    /**
     * @param bool $warning
     */
    public function setWarning(bool $warning)
    {
        $this->warning = $warning;
    }
}

<?php

namespace AppBundle\Entity;

use AppBundle\Constants;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Correction
 *
 * @ORM\Table(name="corrections")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CorrectionRepository")
 */
class Correction
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
     * @var bool
     * @Groups({"correction-edit"})
     *
     * @ORM\Column(name="draft", type="boolean")
     */
    private $draft = true;

    /**
     * @var \DateTime
     * @Groups({"correction-list", "correction-edit", "evaluation-stats"})
     *
     * @ORM\Column(name="date_submission", type="datetime", nullable=true)
     */
    private $dateSubmission;

    /**
     * @var float
     *
     * @ORM\Column(name="mark", type="float", nullable=true)
     * @Groups({"correction-edit", "evaluation-stats", "assignment-corrections", "assignment-correction-list"})
     */
    private $mark;

    /**
     * @var float
     * @Groups({"evaluation-stats"})
     *
     * @ORM\Column(name="reliability", type="float", nullable=true)
     */
    private $reliability;

    /**
     * @var float
     * @Groups({"evaluation-stats"})
     *
     * @ORM\Column(name="recalculated_reliability", type="float", nullable=true)
     */
    private $recalculatedReliability;

    /**
     * @var \AppBundle\Entity\User
     * @Groups({"evaluation-attribution", "evaluation-stats", "assignment-corrections", "assignment-correction-list",
     *     "teacher-correction"})
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     */
    private $user;

    /**
     * @var \AppBundle\Entity\Group
     * @Groups({"evaluation-attribution", "evaluation-stats", "assignment-corrections", "assignment-correction-list",
     *     "teacher-correction"})
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Group")
     */
    private $group;

    /**
     * @var Assignment
     * @Groups({"correction-list", "correction-edit", "assignment-corrections"})
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Assignment", inversedBy="corrections")
     */
    private $assignment;

    /**
     * @var ArrayCollection
     * @Groups({"correction-edit", "evaluation-stats-criterias", "assignment-corrections"})
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\CorrectionSection", mappedBy="correction", cascade={"persist", "remove"}, orphanRemoval=true)
     * @Assert\Valid()
     */
    private $correction_sections;

    /**
     * @var int
     * @Groups({"evaluation-stats", "evaluation-stats-criterias"})
     */
    private $thumbsUp = 0;

    /**
     * @var int
     * @Groups({"evaluation-stats", "evaluation-stats-criterias"})
     */
    private $thumbsDown = 0;

    /**
     * Correction constructor.
     */
    public function __construct()
    {
        $this->correction_sections = new ArrayCollection();
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
    public function getReliability()
    {
        return $this->reliability;
    }

    /**
     * @param float $reliability
     */
    public function setReliability($reliability)
    {
        $this->reliability = $reliability;
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
    public function setUser(User $user)
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
    public function setGroup(Group $group)
    {
        $this->group = $group;
    }

    /**
     * @return Assignment
     */
    public function getAssignment()
    {
        return $this->assignment;
    }

    /**
     * @param Assignment $assignment
     */
    public function setAssignment(Assignment $assignment)
    {
        $this->assignment = $assignment;
    }

    /**
     * @return ArrayCollection
     */
    public function getCorrectionSections()
    {
        return $this->correction_sections;
    }

    /**
     * @param CorrectionSection $correctionSection
     */
    public function addCorrectionSection(CorrectionSection $correctionSection)
    {
        $this->correction_sections[] = $correctionSection;
        $correctionSection->setCorrection($this);
    }

    /**
     * @param CorrectionSection $correctionSection
     */
    public function removeCorrectionSection(CorrectionSection $correctionSection)
    {
        $this->correction_sections->removeElement($correctionSection);
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
     * @return Correction
     */
    public function setMark($mark)
    {
        $this->mark = $mark;

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
     * @return Correction
     */
    public function setRecalculatedReliability($recalculatedReliability)
    {
        $this->recalculatedReliability = $recalculatedReliability;

        return $this;
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
     * Set draft
     *
     * @param boolean $draft
     *
     * @return Correction
     */
    public function setDraft($draft)
    {
        $this->draft = $draft;

        return $this;
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
                    ->setParameter('%var%', Constants::VARS['submitted_correction'])
                    ->atPath('draft')
                    ->setInvalidValue($this->draft)
                    ->addViolation();
            }
        }
    }

    /**
     * @return int
     */
    public function getThumbsUp()
    {
        return $this->thumbsUp;
    }

    /**
     * @param int $thumbsUp
     */
    public function setThumbsUp($thumbsUp)
    {
        $this->thumbsUp = $thumbsUp;
    }

    /**
     * @return int
     */
    public function getThumbsDown()
    {
        return $this->thumbsDown;
    }

    /**
     * @param int $thumbsDown
     */
    public function setThumbsDown($thumbsDown)
    {
        $this->thumbsDown = $thumbsDown;
    }

    /**
     * @return string
     */
    public function getCorrectorName()
    {
        if ($this->assignment->getEvaluation()->getIndividualCorrection()) {
            return $this->user->getLastName() . ' ' . $this->user->getFirstName();
        } else {
            return $this->group->getName();
        }
    }

    /**
     * @return bool
     */
    public function isStudentCorrection()
    {
        return ($this->user && $this->user->getRole()->getId() === Constants::ROLE_STUDENT) || $this->group;
    }

    public function removeCorrectionSections()
    {
        $this->correction_sections = new ArrayCollection();
    }
}

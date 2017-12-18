<?php

namespace AppBundle\Entity;

use AppBundle\Constants;
use AppBundle\Validator\CollectionSameItem;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Evaluation
 *
 * @ORM\Table(name="evaluations")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\EvaluationRepository")
 *
 * @UniqueEntity({"name", "teacher", "lesson"}, message="already_used")
 * @CollectionSameItem(collection="sections", variable="title")
 * @CollectionSameItem(collection="sections", variable="order")
 */
class Evaluation
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
     * @Groups({"assignment-edit", "assignment-list", "evaluation-edit", "evaluation-list",
     *     "correction-list", "lesson-evaluation-list", "correction-edit",
     *     "evaluation-stats", "criteria-charts", "assignment-corrections", "assignment-correction-list", "teacher-lesson"})
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Assert\NotBlank(message="not_blank")
     * @Assert\Regex("/^[0-9a-zA-ZàâäÀÂÄéèêëÉÈÊËîïÎÏùûüÛÙÜôöÔÖçÇ,.;:()_\x22\-\' ]+$/", message="not_valid")
     */
    private $name;

    /**
     * @var string
     * @Groups({"assignment-edit", "assignment-list", "evaluation-edit", "evaluation-list", "correction-edit",
     *     "evaluation-stats"})
     *
     * @ORM\Column(name="subject", type="text", nullable=true)
     */
    private $subject;

    /**
     * @var \DateTime
     * @Groups({"assignment-edit", "assignment-list", "evaluation-edit", "evaluation-list", "assignment-corrections",
     *     "assignment-correction-list", "evaluation-stats"})
     *
     * @ORM\Column(name="date_start_assignment", type="datetime", nullable=true)
     * @Assert\DateTime()
     */
    private $dateStartAssignment;

    /**
     * @var \DateTime
     * @Groups({"assignment-edit", "assignment-list", "evaluation-edit", "evaluation-list", "lesson-evaluation-list",
     *     "assignment-corrections", "assignment-correction-list", "teacher-lesson", "evaluation-stats"})
     *
     * @ORM\Column(name="date_end_assignment", type="datetime", nullable=true)
     * @Assert\DateTime()
     */
    private $dateEndAssignment;

    /**
     * @var \DateTime
     * @Groups({"assignment-list", "evaluation-edit", "evaluation-list",
     *     "correction-list", "correction-edit", "assignment-corrections", "assignment-correction-list",
     *     "evaluation-stats"})
     *
     * @ORM\Column(name="date_start_correction", type="datetime", nullable=true)
     * @Assert\DateTime()
     */
    private $dateStartCorrection;

    /**
     * @var \DateTime
     * @Groups({"assignment-list", "evaluation-edit", "evaluation-list",
     *     "correction-list", "lesson-evaluation-list", "correction-edit", "assignment-corrections",
     *     "assignment-correction-list", "teacher-lesson", "evaluation-stats"})
     *
     * @ORM\Column(name="date_end_correction", type="datetime", nullable=true)
     * @Assert\DateTime()
     */
    private $dateEndCorrection;

    /**
     * @var \DateTime
     * @Groups({"evaluation-edit", "assignment-list", "assignment-corrections", "assignment-correction-list",
     *     "evaluation-stats"})
     *
     * @ORM\Column(name="date_end_opinion", type="datetime", nullable=true)
     * @Assert\DateTime()
     */
    private $dateEndOpinion;

    /**
     * @var int
     * @Groups({"evaluation-edit"})
     *
     * @ORM\Column(name="number_corrections", type="integer", nullable=true)
     * @Assert\GreaterThan(0, message="greater_than_0")
     */
    private $numberCorrections;

    /**
     * @var bool
     * @Groups({"evaluation-edit", "correction-edit", "assignment-corrections", "assignment-correction-list",
     *     "correction-list"})
     *
     * @ORM\Column(name="anonymity", type="boolean")
     * @Assert\NotNull(message="not_null")
     */
    private $anonymity;

    /**
     * @var bool
     * @Groups({"assignment-edit", "assignment-list", "evaluation-edit", "correction-edit",
     *     "evaluation-stats", "assignment-corrections", "assignment-correction-list",
     *     "correction-list"})
     *
     * @ORM\Column(name="individual_assignment", type="boolean")
     * @Assert\NotNull(message="not_null")
     */
    private $individualAssignment;

    /**
     * @var bool
     * @Groups({"assignment-list", "evaluation-edit",
     *     "correction-list", "correction-edit",
     *     "evaluation-stats", "assignment-corrections", "assignment-correction-list"})
     *
     * @ORM\Column(name="individual_correction", type="boolean")
     * @Assert\NotNull(message="not_null")
     */
    private $individualCorrection;

    /**
     * @var string
     * @Groups({"assignment-edit", "evaluation-edit"})
     *
     * @ORM\Column(name="assignment_instructions", type="text", nullable=true)
     */
    private $assignmentInstructions;

    /**
     * @var string
     * @Groups({"evaluation-edit", "correction-edit", "assignment-corrections"})
     *
     * @ORM\Column(name="correction_instructions", type="text", nullable=true)
     */
    private $correctionInstructions;

    /**
     * @var bool
     * @Groups({"assignment-edit", "assignment-list", "evaluation-edit", "evaluation-list", "evaluation-stats"})
     *
     * @ORM\Column(name="active_assignment", type="boolean")
     * @Assert\NotNull(message="not_null")
     */
    private $activeAssignment;

    /**
     * @var bool
     * @Groups({"assignment-list", "evaluation-edit", "evaluation-list", "evaluation-stats"})
     *
     * @ORM\Column(name="active_correction", type="boolean")
     * @Assert\NotNull(message="not_null")
     */
    private $activeCorrection;

    /**
     * @var float
     * @Groups({"evaluation-edit", "evaluation-list", "lesson-evaluation-list",
     *     "evaluation-stats", "teacher-lesson"})
     *
     * @ORM\Column(name="assignment_average", type="float", nullable=true)
     */
    private $assignmentAverage;

    /**
     * @var bool
     * @Groups({"assignment-list", "evaluation-edit", "evaluation-list", "assignment-corrections",
     *     "assignment-correction-list"})
     *
     * @ORM\Column(name="show_assignment_mark", type="boolean")
     * @Assert\NotNull(message="not_null")
     */
    private $showAssignmentMark = true;

    /**
     * @var bool
     * @Groups({"evaluation-edit", "assignment-corrections"})
     *
     * @ORM\Column(name="show_corrections_mark", type="boolean")
     * @Assert\NotNull(message="not_null")
     */
    private $showCorrectionsMark = true;

    /**
     * @var bool
     * @Groups({"evaluation-edit", "assignment-corrections", "assignment-correction-list", "evaluation-stats"})
     *
     * @ORM\Column(name="use_teacher_mark", type="boolean")
     * @Assert\NotNull(message="not_null")
     */
    private $useTeacherMark = false;

    /**
     * @var bool
     * @Groups({"evaluation-edit", "evaluation-list", "assignment-corrections", "assignment-correction-list"})
     *
     * @ORM\Column(name="archived", type="boolean")
     */
    private $archived;

    /**
     * @var \AppBundle\Entity\User
     * @Groups({"evaluation-edit", "lesson-evaluation-list", "teacher-lesson"})
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="teacher_evaluations")
     * @Assert\NotNull(message="not_null")
     */
    private $teacher;

    /**
     * @var \AppBundle\Entity\Lesson
     * @Groups({"assignment-edit", "assignment-list", "evaluation-edit", "evaluation-list",
     *     "correction-list"})
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Lesson", inversedBy="evaluations")
     * @Assert\NotNull(message="not_null")
     */
    private $lesson;

    /**
     * @var MarkMode
     * @Groups({"evaluation-edit", "evaluation-stats"})
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\MarkMode")
     * @ORM\JoinColumn(nullable=false)
     */
    private $mark_mode;

    /**
     * @var MarkPrecisionMode
     * @Groups({"evaluation-edit"})
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\MarkPrecisionMode")
     * @ORM\JoinColumn(nullable=false)
     */
    private $mark_precision_mode;

    /**
     * @var MarkRoundMode
     * @Groups({"evaluation-edit"})
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\MarkRoundMode")
     * @ORM\JoinColumn(nullable=false)
     */
    private $mark_round_mode;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @Groups({"evaluation-edit"})
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\User", mappedBy="evaluations")
     */
    private $users;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @Groups({"evaluation-edit"})
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Group", mappedBy="evaluations")
     */
    private $groups;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @Groups({"evaluation-stats"})
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Assignment", mappedBy="evaluation", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $assignments;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @Groups({"evaluation-edit", "criteria-charts"})
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Section", mappedBy="evaluation", cascade={"persist", "remove"}, orphanRemoval=true)
     * @Assert\Valid()
     */
    private $sections;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @Groups({"evaluation-edit", "assignment-edit"})
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\ExampleAssignment", mappedBy="evaluation", cascade={"persist", "remove"}, orphanRemoval=true)
     * @Assert\Valid()
     */
    private $exampleAssignments;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @Groups({"evaluation-edit", "assignment-edit"})
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\SubjectFile", mappedBy="evaluation", cascade={"persist", "remove"}, orphanRemoval=true)
     * @Assert\Valid()
     */
    private $subjectFiles;

    /**
     * Evaluation constructor.
     */
    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->groups = new ArrayCollection();
        $this->assignments = new ArrayCollection();
        $this->sections = new ArrayCollection();
        $this->exampleAssignments = new ArrayCollection();
        $this->subjectFiles = new ArrayCollection();
    }

    public function __clone()
    {
        $this->id = null;
        $this->activeAssignment = false;
        $this->activeCorrection = false;
        $this->archived = false;
        $this->name .= ' (copie)';
        /** @var User $user */
        foreach ($this->users as $user) {
            $user->addEvaluation($this);
        }
        /** @var Group $group */
        foreach ($this->groups as $group) {
            $group->addEvaluation($this);
        }
        $this->assignments = new ArrayCollection();
        // Clone sections
        $sectionsClone = new ArrayCollection();
        /** @var Section $section */
        foreach ($this->sections as $section) {
            $sectionClone = clone $section;
            $sectionClone->setEvaluation($this);
            $sectionsClone->add($sectionClone);
        }
        $this->sections = $sectionsClone;
        // Clone exampleAssignments
        $examplesClone = new ArrayCollection();
        /** @var ExampleAssignment $exampleAssignment */
        foreach ($this->exampleAssignments as $exampleAssignment) {
            $exampleClone = clone $exampleAssignment;
            $exampleClone->setEvaluation($this);
            $examplesClone->add($exampleClone);
        }
        $this->exampleAssignments = $examplesClone;
        // Clone subjectFiles
        $filesClone = new ArrayCollection();
        /** @var SubjectFile $subjectFile */
        foreach ($this->subjectFiles as $subjectFile) {
            $fileClone = clone $subjectFile;
            $fileClone->setEvaluation($this);
            $filesClone->add($fileClone);
        }
        $this->subjectFiles = $filesClone;
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
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Evaluation
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get subject
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set subject
     *
     * @param string $subject
     *
     * @return Evaluation
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get dateStartCorrection
     *
     * @return \DateTime
     */
    public function getDateStartCorrection()
    {
        return $this->dateStartCorrection;
    }

    /**
     * Set dateStartCorrection
     *
     * @param \DateTime $dateStartCorrection
     *
     * @return Evaluation
     */
    public function setDateStartCorrection($dateStartCorrection)
    {
        $this->dateStartCorrection = $dateStartCorrection;

        return $this;
    }

    /**
     * Get dateEndCorrection
     *
     * @return \DateTime
     */
    public function getDateEndCorrection()
    {
        return $this->dateEndCorrection;
    }

    /**
     * Set dateEndCorrection
     *
     * @param \DateTime $dateEndCorrection
     *
     * @return Evaluation
     */
    public function setDateEndCorrection($dateEndCorrection)
    {
        $this->dateEndCorrection = $dateEndCorrection;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateEndOpinion()
    {
        return $this->dateEndOpinion;
    }

    /**
     * @param \DateTime $dateEndOpinion
     *
     * @return Evaluation
     */
    public function setDateEndOpinion($dateEndOpinion)
    {
        $this->dateEndOpinion = $dateEndOpinion;

        return $this;
    }

    /**
     * Get numberCorrections
     *
     * @return int
     */
    public function getNumberCorrections()
    {
        return $this->numberCorrections;
    }

    /**
     * Set numberCorrections
     *
     * @param integer $numberCorrections
     *
     * @return Evaluation
     */
    public function setNumberCorrections($numberCorrections)
    {
        $this->numberCorrections = $numberCorrections;

        return $this;
    }

    /**
     * Get anonymity
     *
     * @return bool
     */
    public function getAnonymity()
    {
        return $this->anonymity;
    }

    /**
     * Set anonymity
     *
     * @param boolean $anonymity
     *
     * @return Evaluation
     */
    public function setAnonymity($anonymity)
    {
        $this->anonymity = $anonymity;

        return $this;
    }

    /**
     * Get individualAssignment
     *
     * @return bool
     */
    public function getIndividualAssignment()
    {
        return $this->individualAssignment;
    }

    /**
     * Set individualAssignment
     *
     * @param boolean $individualAssignment
     *
     * @return Evaluation
     */
    public function setIndividualAssignment($individualAssignment)
    {
        $this->individualAssignment = $individualAssignment;

        return $this;
    }

    /**
     * Get individualCorrection
     *
     * @return bool
     */
    public function getIndividualCorrection()
    {
        return $this->individualCorrection;
    }

    /**
     * Set individualCorrection
     *
     * @param boolean $individualCorrection
     *
     * @return Evaluation
     */
    public function setIndividualCorrection($individualCorrection)
    {
        $this->individualCorrection = $individualCorrection;

        return $this;
    }

    /**
     * Get assignment instructions
     *
     * @return string
     */
    public function getAssignmentInstructions()
    {
        return $this->assignmentInstructions;
    }

    /**
     * Set assignment instructions
     *
     * @param string $assignmentInstructions
     *
     * @return Evaluation
     */
    public function setAssignmentInstructions($assignmentInstructions)
    {
        $this->assignmentInstructions = $assignmentInstructions;

        return $this;
    }

    /**
     * Get correction instructions
     *
     * @return string
     */
    public function getCorrectionInstructions()
    {
        return $this->correctionInstructions;
    }

    /**
     * Set correction instructions
     *
     * @param string $correctionInstructions
     *
     * @return Evaluation
     */
    public function setCorrectionInstructions($correctionInstructions)
    {
        $this->correctionInstructions = $correctionInstructions;

        return $this;
    }

    /**
     * Get activeAssignment
     *
     * @return bool
     */
    public function getActiveAssignment()
    {
        return $this->activeAssignment;
    }

    /**
     * Set activeAssignment
     *
     * @param boolean $activeAssignment
     *
     * @return Evaluation
     */
    public function setActiveAssignment($activeAssignment)
    {
        $this->activeAssignment = $activeAssignment;

        return $this;
    }

    /**
     * Get activeCorrection
     *
     * @return bool
     */
    public function getActiveCorrection()
    {
        return $this->activeCorrection;
    }

    /**
     * Set activeCorrection
     *
     * @param boolean $activeCorrection
     *
     * @return Evaluation
     */
    public function setActiveCorrection($activeCorrection)
    {
        $this->activeCorrection = $activeCorrection;

        return $this;
    }

    /**
     * Get assignmentAverage
     *
     * @return float
     */
    public function getAssignmentAverage()
    {
        return $this->assignmentAverage;
    }

    /**
     * Set assignmentAverage
     *
     * @param float $assignmentAverage
     *
     * @return Evaluation
     */
    public function setAssignmentAverage($assignmentAverage)
    {
        $this->assignmentAverage = $assignmentAverage;

        return $this;
    }

    /**
     * Get showAssignmentMark
     *
     * @return bool
     */
    public function getShowAssignmentMark()
    {
        return $this->showAssignmentMark;
    }

    /**
     * Set showAssignmentMark
     *
     * @param boolean $showAssignmentMark
     *
     * @return Evaluation
     */
    public function setShowAssignmentMark($showAssignmentMark)
    {
        $this->showAssignmentMark = $showAssignmentMark;

        return $this;
    }

    /**
     * Get showCorrectionsMark
     *
     * @return boolean
     */
    public function getShowCorrectionsMark()
    {
        return $this->showCorrectionsMark;
    }

    /**
     * Set showCorrectionsMark
     *
     * @param boolean $showCorrectionsMark
     *
     * @return Evaluation
     */
    public function setShowCorrectionsMark($showCorrectionsMark)
    {
        $this->showCorrectionsMark = $showCorrectionsMark;

        return $this;
    }

    /**
     * Get useTeacherMark
     *
     * @return bool
     */
    public function getUseTeacherMark()
    {
        return $this->useTeacherMark;
    }

    /**
     * Set useTeacherMark
     *
     * @param boolean $useTeacherMark
     *
     * @return Evaluation
     */
    public function setUseTeacherMark($useTeacherMark)
    {
        $this->useTeacherMark = $useTeacherMark;

        return $this;
    }

    /**
     * Get archived
     *
     * @return boolean
     */
    public function getArchived()
    {
        return $this->archived;
    }

    /**
     * Set archived
     *
     * @param boolean $archived
     *
     * @return Evaluation
     */
    public function setArchived($archived)
    {
        $this->archived = $archived;

        return $this;
    }

    /**
     * @return User
     */
    public function getTeacher()
    {
        return $this->teacher;
    }

    /**
     * @param User $teacher
     * @return Evaluation
     */
    public function setTeacher($teacher)
    {
        $this->teacher = $teacher;

        return $this;
    }

    /**
     * @return Lesson
     */
    public function getLesson()
    {
        return $this->lesson;
    }

    /**
     * @param Lesson $lesson
     * @return Evaluation
     */
    public function setLesson($lesson)
    {
        $this->lesson = $lesson;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @param User $user
     */
    public function addUser(User $user)
    {
        $this->users[] = $user;
        $user->addEvaluation($this);
    }

    public function removeAllUsers()
    {
        foreach ($this->users as $user) {
            $this->removeUser($user);
        }
    }

    /**
     * @param User $user
     */
    public function removeUser(User $user)
    {
        $this->users->removeElement($user);
        $user->removeEvaluation($this);
    }

    /**
     * @return ArrayCollection
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @param Group $group
     */
    public function addGroup(Group $group)
    {
        $this->groups[] = $group;
        $group->addEvaluation($this);
    }

    public function removeAllGroups()
    {
        foreach ($this->groups as $group) {
            $this->removeGroup($group);
        }
    }

    /**
     * @param Group $group
     */
    public function removeGroup(Group $group)
    {
        $this->groups->removeElement($group);
        $group->removeEvaluation($this);
    }

    /**
     * @return ArrayCollection
     */
    public function getAssignments()
    {
        return $this->assignments;
    }

    /**
     * @param Assignment $assignment
     */
    public function addAssignment(Assignment $assignment)
    {
        $this->assignments[] = $assignment;
        $assignment->setEvaluation($this);
    }

    public function removeAllAssignments()
    {
        foreach ($this->assignments as $assignment) {
            $this->removeAssignment($assignment);
        }
    }

    /**
     * @param Assignment $assignment
     */
    public function removeAssignment(Assignment $assignment)
    {
        $this->assignments->removeElement($assignment);
    }

    /**
     * @return ArrayCollection
     */
    public function getSections()
    {
        return $this->sections;
    }

    /**
     * @param Section $section
     * @return $this
     */
    public function addSection(Section $section)
    {
        $this->sections[] = $section;
        $section->setEvaluation($this);

        return $this;
    }

    /**
     * @param Section $section
     */
    public function removeSection(Section $section)
    {
        $this->sections->removeElement($section);
    }

    /**
     * @return ArrayCollection
     */
    public function getExampleAssignments()
    {
        return $this->exampleAssignments;
    }

    /**
     * @param ExampleAssignment $exampleAssignment
     * @return $this
     */
    public function addExampleAssignment(ExampleAssignment $exampleAssignment)
    {
        $this->exampleAssignments[] = $exampleAssignment;
        $exampleAssignment->setEvaluation($this);

        return $this;
    }

    /**
     * @param ExampleAssignment $exampleAssignment
     */
    public function removeExampleAssignment(ExampleAssignment $exampleAssignment)
    {
        $this->exampleAssignments->removeElement($exampleAssignment);
    }

    /**
     * @return ArrayCollection
     */
    public function getSubjectFiles()
    {
        return $this->subjectFiles;
    }

    /**
     * @param SubjectFile $subjectFile
     * @return $this
     */
    public function addSubjectFile(SubjectFile $subjectFile)
    {
        $this->subjectFiles[] = $subjectFile;
        $subjectFile->setEvaluation($this);

        return $this;
    }

    /**
     * @param SubjectFile $subjectFile
     */
    public function removeSubjectFile(SubjectFile $subjectFile)
    {
        $this->subjectFiles->removeElement($subjectFile);
    }

    /**
     * Get markMode
     *
     * @return MarkMode
     */
    public function getMarkMode()
    {
        return $this->mark_mode;
    }

    /**
     * Set markMode
     *
     * @param MarkMode $markMode
     *
     * @return Evaluation
     */
    public function setMarkMode(MarkMode $markMode = null)
    {
        $this->mark_mode = $markMode;

        return $this;
    }

    /**
     * Get markPrecisionMode
     *
     * @return MarkPrecisionMode
     */
    public function getMarkPrecisionMode()
    {
        return $this->mark_precision_mode;
    }

    /**
     * Set markPrecisionMode
     *
     * @param MarkPrecisionMode $markPrecisionMode
     *
     * @return Evaluation
     */
    public function setMarkPrecisionMode(MarkPrecisionMode $markPrecisionMode = null)
    {
        $this->mark_precision_mode = $markPrecisionMode;

        return $this;
    }

    /**
     * Get markRoundMode
     *
     * @return \AppBundle\Entity\MarkRoundMode
     */
    public function getMarkRoundMode()
    {
        return $this->mark_round_mode;
    }

    /**
     * Set markRoundMode
     *
     * @param MarkRoundMode $markRoundMode
     *
     * @return Evaluation
     */
    public function setMarkRoundMode(MarkRoundMode $markRoundMode = null)
    {
        $this->mark_round_mode = $markRoundMode;

        return $this;
    }

    /**
     * @param ExecutionContextInterface $context
     *
     * @Assert\Callback
     */
    public function areDatesAssignmentValid(ExecutionContextInterface $context)
    {
        if ($this->dateStartAssignment == null xor $this->dateEndAssignment == null) {
            $field = $this->dateStartAssignment == null ? 'date_start_assignment' : 'date_end_assignment';
            $context
                ->buildViolation('not_null')
                ->atPath($field)
                ->setInvalidValue(null)
                ->addViolation();
        }
        if ($this->dateEndAssignment != null && $this->dateStartAssignment != null) {
            if ($this->getDateEndAssignment() <= $this->getDateStartAssignment()) {
                $context
                    ->buildViolation('greater_than_var')
                    ->setParameter('%var%', Constants::VARS['date_start_assignment'])
                    ->setParameter('%value%', $this->dateStartAssignment->format(Constants::DATE_FORMAT))
                    ->atPath('date_end_assignment')
                    ->setInvalidValue($this->dateEndAssignment)
                    ->addViolation();
            }
        }
    }

    /**
     * Get dateEndAssignment
     *
     * @return \DateTime
     */
    public function getDateEndAssignment()
    {
        return $this->dateEndAssignment;
    }

    /**
     * Set dateEndAssignment
     *
     * @param \DateTime $dateEndAssignment
     *
     * @return Evaluation
     */
    public function setDateEndAssignment($dateEndAssignment)
    {
        $this->dateEndAssignment = $dateEndAssignment;

        return $this;
    }

    /**
     * Get dateStartAssignment
     *
     * @return \DateTime
     */
    public function getDateStartAssignment()
    {
        return $this->dateStartAssignment;
    }

    /**
     * Set dateStartAssignment
     *
     * @param \DateTime $dateStartAssignment
     *
     * @return Evaluation
     */
    public function setDateStartAssignment($dateStartAssignment)
    {
        $this->dateStartAssignment = $dateStartAssignment;

        return $this;
    }

    /**
     * @param ExecutionContextInterface $context
     *
     * @Assert\Callback
     */
    public function areDatesCorrectionValid(ExecutionContextInterface $context)
    {
        if ($this->dateStartCorrection == null xor $this->dateEndCorrection == null) {
            $field = $this->dateStartCorrection == null ? 'date_start_correction' : 'date_end_correction';
            $context
                ->buildViolation('not_null')
                ->atPath($field)
                ->setInvalidValue(null)
                ->addViolation();
        }
        if ($this->dateEndCorrection != null && $this->dateStartCorrection != null) {
            if ($this->dateEndCorrection <= $this->dateStartCorrection) {
                $context
                    ->buildViolation('greater_than_var')
                    ->setParameter('%var%', Constants::VARS['date_start_correction'])
                    ->setParameter('%value%', $this->dateStartCorrection->format(Constants::DATE_FORMAT))
                    ->atPath('date_end_correction')
                    ->setInvalidValue($this->dateEndCorrection)
                    ->addViolation();
            }
            if ($this->dateEndCorrection <= $this->dateEndAssignment) {
                $context
                    ->buildViolation('greater_than_var')
                    ->setParameter('%var%', Constants::VARS['date_end_assignment'])
                    ->setParameter('%value%', $this->dateEndAssignment->format(Constants::DATE_FORMAT))
                    ->atPath('date_end_correction')
                    ->setInvalidValue($this->dateEndCorrection)
                    ->addViolation();

                $context
                    ->buildViolation('lower_than_var')
                    ->setParameter('%var%', Constants::VARS['date_end_correction'])
                    ->setParameter('%value%', $this->dateEndCorrection->format(Constants::DATE_FORMAT))
                    ->atPath('date_end_assignment')
                    ->setInvalidValue($this->dateEndAssignment)
                    ->addViolation();
            }
        }
        if ($this->dateEndOpinion !== null) {
            if ($this->dateEndOpinion < $this->dateEndCorrection) {
                $context
                    ->buildViolation('greater_than_var')
                    ->setParameter('%var%', Constants::VARS['date_end_correction'])
                    ->setParameter('%value%', $this->dateEndCorrection->format(Constants::DATE_FORMAT))
                    ->atPath('date_end_opinion')
                    ->setInvalidValue($this->dateEndCorrection)
                    ->addViolation();
            }
        }
    }

    /**
     * @param ExecutionContextInterface $context
     *
     * @Assert\Callback()
     */
    public function checkBeforeAssignmentActivation(ExecutionContextInterface $context)
    {
        if ($this->activeAssignment) {
            if (!$this->dateStartAssignment) {
                $context
                    ->buildViolation('not_null')
                    ->atPath('date_start_assignment')
                    ->setInvalidValue($this->dateStartAssignment)
                    ->addViolation();
            }
            if (!$this->dateEndAssignment) {
                $context
                    ->buildViolation('not_null')
                    ->atPath('date_end_assignment')
                    ->setInvalidValue($this->dateEndAssignment)
                    ->addViolation();
            }
            if (count($this->sections) < 1) {
                $context
                    ->buildViolation('at_least_1_section')
                    ->atPath('sections')
                    ->setInvalidValue(count($this->sections))
                    ->addViolation();
            }
            if ($this->individualAssignment) {
                if (count($this->users) < 2) {
                    $context
                        ->buildViolation('at_least_2_users')
                        ->atPath('users')
                        ->setInvalidValue(count($this->users))
                        ->addViolation();
                }
            } else {
                if (count($this->groups) < 2) {
                    $context
                        ->buildViolation('at_least_2_groups')
                        ->atPath('groups')
                        ->setInvalidValue(count($this->groups))
                        ->addViolation();
                }
            }
        }
    }

    /**
     * @param ExecutionContextInterface $context
     *
     * @Assert\Callback()
     */
    public function checkNumberCorrections(ExecutionContextInterface $context)
    {
        if ($this->individualAssignment) {
            if ($this->individualCorrection) {
                // Individual assignment, individual correction
                if ($this->numberCorrections > count($this->users) - 1) {
                    $context
                        ->buildViolation('lower_than_var')
                        ->setParameter('%var%', Constants::VARS['num_users'])
                        ->setParameter('%value%', count($this->users))
                        ->atPath('number_corrections')
                        ->setInvalidValue($this->numberCorrections)
                        ->addViolation();
                }
            } else {
                // Individual assignment, group correction

            }
        } else {
            // Group assignment: max number corrections = count(groups) - 1
            if ($this->numberCorrections > count($this->groups) - 1) {
                $context
                    ->buildViolation('lower_than_var')
                    ->setParameter('%var%', Constants::VARS['num_groups'])
                    ->setParameter('%value%', count($this->groups))
                    ->atPath('number_corrections')
                    ->setInvalidValue($this->numberCorrections)
                    ->addViolation();
            }
        }
    }

    /**
     * @param ExecutionContextInterface $context
     *
     * @Assert\Callback()
     */
    public function checkBeforeCorrectionActivation(ExecutionContextInterface $context)
    {
        if ($this->activeCorrection) {
            if (!$this->activeAssignment) {
                $context
                    ->buildViolation('assignment_must_be_active')
                    ->atPath('active_assignment')
                    ->setInvalidValue($this->activeAssignment)
                    ->addViolation();
            }
            if (!$this->dateStartCorrection) {
                $context
                    ->buildViolation('not_null')
                    ->atPath('date_start_correction')
                    ->setInvalidValue($this->dateStartCorrection)
                    ->addViolation();
            }
            if (!$this->dateEndCorrection) {
                $context
                    ->buildViolation('not_null')
                    ->atPath('date_end_correction')
                    ->setInvalidValue($this->dateEndCorrection)
                    ->addViolation();
            }
            if (!$this->numberCorrections) {
                $context
                    ->buildViolation('not_null')
                    ->atPath('number_corrections')
                    ->setInvalidValue($this->numberCorrections)
                    ->addViolation();
            }
            /** @var Section $section */
            foreach ($this->sections as $section) {
                if (count($section->getCriterias()) < 1) {
                    $context
                        ->buildViolation('at_least_1_criteria')
                        ->atPath('criterias')
                        ->setInvalidValue(null)
                        ->addViolation();
                }
            }
        }
    }

    /**
     * @param ExecutionContextInterface $context
     *
     * @Assert\Callback()
     */
    public function checkGroups(ExecutionContextInterface $context)
    {
        if ($this->individualAssignment) {
            if (count($this->groups) > 0) {
                $context
                    ->buildViolation('cannot_attribute')
                    ->setParameter('%var%', Constants::VARS['groups'])
                    ->setParameter('%individual%', Constants::VARS['individual_assignment'])
                    ->atPath('groups')
                    ->setInvalidValue($this->groups)
                    ->addViolation();
            }
        } else {
            if (count($this->users) > 0) {
                $context
                    ->buildViolation('cannot_attribute')
                    ->setParameter('%var%', Constants::VARS['users'])
                    ->setParameter('%individual%', Constants::VARS['group_assignment'])
                    ->atPath('users')
                    ->setInvalidValue($this->users)
                    ->addViolation();
            }
            /** @var Group $group */
            foreach ($this->groups as $groupId => $group) {
                /** @var User $user */
                foreach ($group->getUsers() as $user) {
                    /** @var Group $groupCheck */
                    foreach ($this->groups as $groupCheck) {
                        if ($groupCheck !== $group) {
                            if ($groupCheck->getUsers()->contains($user)) {
                                $context
                                    ->buildViolation('user_in_groups')
                                    ->atPath('groups[' . $groupId . '].users')
                                    ->setInvalidValue($user->getLastName() . ' ' . $user->getFirstName())
                                    ->addViolation();
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @param ExecutionContextInterface $context
     *
     * @Assert\Callback()
     */
    public function checkNotImplemented(ExecutionContextInterface $context)
    {
        if ($this->individualAssignment && !$this->individualCorrection) {
            $context
                ->buildViolation('not_implemented')
                ->atPath('individual_correction')
                ->setInvalidValue($this->individualCorrection)
                ->addViolation();
        }
    }
}
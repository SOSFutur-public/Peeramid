<?php

namespace AppBundle\Entity;

use AppBundle\Constants;
use AppBundle\Validator\CollectionSameItem;
use AppBundle\Validator\MaxSize;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Section
 *
 * @ORM\Table(name="sections")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SectionRepository")
 *
 * @CollectionSameItem(collection="criterias", variable="description")
 * @CollectionSameItem(collection="criterias", variable="order")
 */
class Section
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
     * @Groups({"evaluation-edit", "assignment-edit", "correction-edit", "criteria-charts", "assignment-corrections"})
     *
     * @ORM\Column(name="title", type="string", length=191)
     * @Assert\NotBlank(message="not_blank")
     */
    private $title;

    /**
     * @var string
     * @Groups({"evaluation-edit", "assignment-edit", "correction-edit", "assignment-corrections"})
     *
     * @ORM\Column(name="subject", type="text")
     * @Assert\NotBlank(message="not_blank")
     */
    private $subject;

    /**
     * @var int
     * @Groups({"evaluation-edit", "assignment-edit", "correction-edit", "criteria-charts", "assignment-corrections",
     *     "evaluation-stats"})
     *
     * @ORM\Column(name="`order`", type="integer")
     * @Assert\NotBlank(message="not_blank")
     * @Assert\GreaterThan(0, message="greater_than_0")
     */
    private $order;

    /**
     * Max size (in MB)
     * @var int
     * @Groups({"evaluation-edit", "assignment-edit"})
     *
     * @ORM\Column(name="max_size", type="integer", nullable=true)
     * @Assert\GreaterThan(0, message="greater_than_0")
     * @MaxSize()
     */
    private $maxSize;

    /**
     * @var bool
     * @Groups({"evaluation-edit", "assignment-edit"})
     *
     * @ORM\Column(name="limit_file_types", type="boolean", nullable=true)
     */
    private $limitFileTypes;

    /**
     * @var \AppBundle\Entity\SectionType
     * @Groups({"evaluation-edit", "assignment-edit", "correction-edit", "assignment-corrections"})
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\SectionType", cascade={"persist"})
     * @ORM\JoinColumn(name="section_type", referencedColumnName="id")
     * @Assert\NotNull(message="not_null")
     */
    private $section_type;

    /**
     * @var \AppBundle\Entity\Evaluation
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Evaluation", cascade={"persist"})
     */
    private $evaluation;

    /**
     * @var ArrayCollection
     * @Groups({"evaluation-edit", "assignment-feedback", "criteria-charts"})
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Criteria", mappedBy="section", cascade={"persist", "remove"}, orphanRemoval=true)
     * @Assert\Valid()
     */
    private $criterias;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\AssignmentSection", mappedBy="section", cascade={"remove"}, orphanRemoval=true)
     */
    private $assignment_sections;

    /**
     * @var ArrayCollection
     * @Groups({"evaluation-edit", "assignment-edit"})
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\FileType")
     * @ORM\JoinTable(name="section_has_file_type")
     */
    private $file_types;

    /**
     * Section constructor.
     */
    public function __construct()
    {
        $this->file_types = new ArrayCollection();
        $this->criterias = new ArrayCollection();
        $this->file_types = new ArrayCollection();
        $this->assignment_sections = new ArrayCollection();
    }

    public function __clone()
    {
        $this->id = null;
        $this->assignment_sections = new ArrayCollection();
        $criteriasClone = new ArrayCollection();
        /** @var Criteria $criteria */
        foreach ($this->criterias as $criteria) {
            $criteriaClone = clone $criteria;
            $criteriaClone->setSection($this);
            $criteriasClone->add($criteriaClone);
        }
        $this->criterias = $criteriasClone;
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
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return Section
     */
    public function setTitle($title)
    {
        $this->title = $title;

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
     * @return Section
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

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
     * @return Section
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * @return int
     */
    public function getMaxSize()
    {
        return $this->maxSize;
    }

    /**
     * @param int $maxSize
     *
     * @return $this
     */
    public function setMaxSize($maxSize)
    {
        $this->maxSize = $maxSize;

        return $this;
    }

    /**
     * @return SectionType
     */
    public function getSectionType()
    {
        return $this->section_type;
    }

    /**
     * @param SectionType $section_type
     */
    public function setSectionType($section_type)
    {
        $this->section_type = $section_type;
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
    public function setEvaluation($evaluation)
    {
        $this->evaluation = $evaluation;
    }

    /**
     * @return ArrayCollection
     */
    public function getCriterias()
    {
        return $this->criterias;
    }

    /**
     * @param Criteria $criteria
     */
    public function addCriteria(Criteria $criteria)
    {
        $this->criterias[] = $criteria;
        $criteria->setSection($this);
    }

    /**
     * @param Criteria $criteria
     */
    public function removeCriteria(Criteria $criteria)
    {
        $this->criterias->removeElement($criteria);
    }

    /**
     * @return ArrayCollection
     */
    public function getAssignmentSections()
    {
        return $this->assignment_sections;
    }

    /**
     * @param AssignmentSection $assignmentSection
     */
    public function addAssignmentSection(AssignmentSection $assignmentSection)
    {
        $this->assignment_sections[] = $assignmentSection;
        $assignmentSection->setSection($this);
    }

    /**
     * @param AssignmentSection $assignmentSection
     */
    public function removeAssignmentSection(AssignmentSection $assignmentSection)
    {
        $this->assignment_sections->removeElement($assignmentSection);
    }

    /**
     * @return ArrayCollection
     */
    public function getFileTypes()
    {
        return $this->file_types;
    }

    /**
     * @param FileType $fileType
     */
    public function addFileType(FileType $fileType)
    {
        $this->file_types[] = $fileType;
    }

    /**
     * @param FileType $fileType
     */
    public function removeFileType(FileType $fileType)
    {
        $this->file_types->removeElement($fileType);
    }

    /**
     * @return bool
     */
    public function isLimitFileTypes()
    {
        return $this->limitFileTypes;
    }

    /**
     * @param bool $limitFileTypes
     */
    public function setLimitFileTypes($limitFileTypes)
    {
        $this->limitFileTypes = $limitFileTypes;
    }

    /**
     * @param ExecutionContextInterface $context
     *
     * @Assert\Callback
     */
    public function checkFileTypes(ExecutionContextInterface $context)
    {
        if ($this->section_type) {
            if ($this->section_type->getId() == Constants::SECTION_TYPE_FILE) {
                if ($this->limitFileTypes) {
                    if (count($this->file_types) < 1) {
                        $context
                            ->buildViolation('not_null')
                            ->atPath('file_types')
                            ->setInvalidValue(array())
                            ->addViolation();
                    }
                }
            }
        }
    }
}


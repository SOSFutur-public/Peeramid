<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;

/**
 * AssignmentSection
 *
 * @ORM\Table(name="assignment_sections")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\AssignmentSectionRepository")
 */
class AssignmentSection
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
     * @Groups({"assignment-edit", "correction-edit", "assignment-corrections"})
     *
     * @ORM\Column(name="answer", type="text", nullable=true)
     */
    private $answer;

    /**
     * @var \AppBundle\Entity\Assignment
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Assignment")
     */
    private $assignment;

    /**
     * @var \AppBundle\Entity\Section
     * @Groups({"assignment-edit", "correction-edit", "assignment-corrections"})
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Section", inversedBy="assignment_sections")
     */
    private $section;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\CorrectionSection", mappedBy="assignment_section", cascade={"remove"}, orphanRemoval=true)
     */
    private $correction_sections;

    /**
     * @var ArrayCollection
     * @Groups({"evaluation-stats-criterias"})
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\AssignmentCriteria", mappedBy="assignment_section", cascade={"persist"}, orphanRemoval=true)
     */
    private $assignment_criterias;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->correction_sections = new ArrayCollection();
        $this->assignment_criterias = new ArrayCollection();
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
     * @return string
     */
    public function getAnswer()
    {
        return $this->answer;
    }

    /**
     * @param string $answer
     */
    public function setAnswer($answer)
    {
        $this->answer = $answer;
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
     * @return Section
     */
    public function getSection()
    {
        return $this->section;
    }

    /**
     * @param Section $section
     */
    public function setSection(Section $section = null)
    {
        $this->section = $section;
    }

    /**
     * @return ArrayCollection
     */
    public function getCorrectionSections()
    {
        return $this->correction_sections;
    }

    /**
     * Add correctionSection
     *
     * @param CorrectionSection $correctionSection
     *
     * @return AssignmentSection
     */
    public function addCorrectionSection(CorrectionSection $correctionSection)
    {
        $this->correction_sections[] = $correctionSection;
        $correctionSection->setAssignmentSection($this);

        return $this;
    }

    /**
     * Remove correctionSection
     *
     * @param CorrectionSection $correctionSection
     */
    public function removeCorrectionSection(CorrectionSection $correctionSection)
    {
        $this->correction_sections->removeElement($correctionSection);
    }

    /**
     * Add assignmentCriteria
     *
     * @param AssignmentCriteria $assignmentCriteria
     *
     * @return AssignmentSection
     */
    public function addAssignmentCriteria(AssignmentCriteria $assignmentCriteria)
    {
        $this->assignment_criterias[] = $assignmentCriteria;
        $assignmentCriteria->setAssignmentSection($this);

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

    public function removeAssignmentCriterias()
    {
        $this->assignment_criterias->clear();
    }
}

<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * CorrectionSection
 *
 * @ORM\Table(name="correction_sections")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CorrectionSectionRepository")
 */
class CorrectionSection
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
     * @var Correction
     * @Groups({"assignment-corrections"})
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Correction")
     */
    private $correction;

    /**
     * @var \AppBundle\Entity\AssignmentSection
     * @Groups({"correction-edit", "assignment-corrections"})
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\AssignmentSection", inversedBy="correction_sections")
     */
    private $assignment_section;

    /**
     * @var ArrayCollection
     * @Groups({"correction-edit", "evaluation-stats-criterias", "assignment-corrections"})
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\CorrectionCriteria", mappedBy="correction_section", cascade={"persist", "remove"}, orphanRemoval=true)
     * @Assert\Valid()
     */
    private $correction_criterias;


    public function __construct()
    {
        $this->correction_criterias = new ArrayCollection();
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
     * Get correction
     *
     * @return Correction
     */
    public function getCorrection()
    {
        return $this->correction;
    }

    /**
     * Set correction
     *
     * @param Correction $correction
     *
     * @return CorrectionSection
     */
    public function setCorrection(Correction $correction = null)
    {
        $this->correction = $correction;

        return $this;
    }

    /**
     * Get assignmentSection
     *
     * @return AssignmentSection
     */
    public function getAssignmentSection()
    {
        return $this->assignment_section;
    }

    /**
     * Set assignmentSection
     *
     * @param AssignmentSection $assignmentSection
     *
     * @return CorrectionSection
     */
    public function setAssignmentSection(AssignmentSection $assignmentSection = null)
    {
        $this->assignment_section = $assignmentSection;

        return $this;
    }

    /**
     * Add correctionCriteria
     *
     * @param CorrectionCriteria $correctionCriteria
     *
     * @return CorrectionSection
     */
    public function addCorrectionCriteria(CorrectionCriteria $correctionCriteria)
    {
        $this->correction_criterias[] = $correctionCriteria;
        $correctionCriteria->setCorrectionSection($this);

        return $this;
    }

    /**
     * Remove correctionCriteria
     *
     * @param CorrectionCriteria $correctionCriteria
     */
    public function removeCorrectionCriteria(CorrectionCriteria $correctionCriteria)
    {
        $this->correction_criterias->removeElement($correctionCriteria);
    }

    /**
     * Get correctionCriterias
     *
     * @return ArrayCollection
     */
    public function getCorrectionCriterias()
    {
        return $this->correction_criterias;
    }

    public function removeCorrectionCriterias()
    {
        $this->correction_criterias = new ArrayCollection();
    }
}

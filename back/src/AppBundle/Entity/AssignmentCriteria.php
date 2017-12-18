<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;

/**
 * AssignmentCriteria
 *
 * @ORM\Table(name="assignment_criterias")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\AssignmentCriteriaRepository")
 */
class AssignmentCriteria
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
     * @Groups({"evaluation-stats-criterias"})
     *
     * @ORM\Column(name="raw_mark", type="float", nullable=true)
     */
    private $rawMark;

    /**
     * @var float
     * @Groups({"evaluation-stats-criterias"})
     *
     * @ORM\Column(name="standard_deviation", type="float", nullable=true)
     */
    private $standardDeviation;

    /**
     * @var float
     * @Groups({"evaluation-stats-criterias"})
     *
     * @ORM\Column(name="weighted_mark", type="float", nullable=true)
     */
    private $weightedMark;

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
     * @ORM\Column(name="mark", type="float", nullable=true)
     */
    private $mark;

    /**
     * @var AssignmentSection
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\AssignmentSection")
     */
    private $assignment_section;

    /**
     * @var Criteria
     * @Groups({"evaluation-stats-criterias"})
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Criteria", inversedBy="assignment_criterias")
     */
    private $criteria;


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
     * @return AssignmentCriteria
     */
    public function setMark($mark)
    {
        $this->mark = $mark;

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
     * @return AssignmentCriteria
     */
    public function setAssignmentSection(AssignmentSection $assignmentSection = null)
    {
        $this->assignment_section = $assignmentSection;

        return $this;
    }

    /**
     * Get criteria
     *
     * @return Criteria
     */
    public function getCriteria()
    {
        return $this->criteria;
    }

    /**
     * Set criteria
     *
     * @param Criteria $criteria
     *
     * @return AssignmentCriteria
     */
    public function setCriteria(Criteria $criteria = null)
    {
        $this->criteria = $criteria;

        return $this;
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
     * @return AssignmentCriteria
     */
    public function setRawMark($rawMark)
    {
        $this->rawMark = $rawMark;

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
     * @return AssignmentCriteria
     */
    public function setStandardDeviation($standardDeviation)
    {
        $this->standardDeviation = $standardDeviation;

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
     * @return AssignmentCriteria
     */
    public function setWeightedMark($weightedMark)
    {
        $this->weightedMark = $weightedMark;

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
     * @return AssignmentCriteria
     */
    public function setReliability($reliability)
    {
        $this->reliability = $reliability;

        return $this;
    }
}

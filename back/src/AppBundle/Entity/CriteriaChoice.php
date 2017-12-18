<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToOne;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * CriteriaChoice
 *
 * @ORM\Table(name="criteria_choices")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CriteriaChoiceRepository")
 */
class CriteriaChoice
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
     * @Groups({"evaluation-edit", "correction-edit", "assignment-corrections"})
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Assert\NotBlank(message="not_blank")
     */
    private $name;

    /**
     * @var float
     * @Groups({"evaluation-edit", "correction-edit", "assignment-corrections"})
     *
     * @ORM\Column(name="mark", type="float")
     * @Assert\NotBlank(message="not_blank")
     */
    private $mark;

    /**
     * @var Criteria
     *
     * @ManyToOne(targetEntity="AppBundle\Entity\Criteria", inversedBy="criteria_choices")
     */
    private $criteria;

    public function __clone()
    {
        $this->id = null;
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
     * @return CriteriaChoice
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
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
     * @return CriteriaChoice
     */
    public function setMark($mark)
    {
        $this->mark = $mark;

        return $this;
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
     * @return CriteriaChoice
     */
    public function setCriteria($criteria)
    {
        $this->criteria = $criteria;

        return $this;
    }
}


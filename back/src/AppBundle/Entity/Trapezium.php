<?php

namespace AppBundle\Entity;

use AppBundle\Constants;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Trapezium
 *
 * @ORM\Table(name="trapeziums")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TrapeziumRepository")
 */
class Trapezium
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
     * @ORM\Column(type="float")
     *
     * @Groups({"trapezium-edit"})
     */
    private $min0;

    /**
     * @var float
     * @ORM\Column(type="float")
     *
     * @Groups({"trapezium-edit"})
     */
    private $min100;

    /**
     * @var float
     * @ORM\Column(type="float")
     *
     * @Groups({"trapezium-edit"})
     */
    private $max100;

    /**
     * @var float
     * @ORM\Column(type="float")
     *
     * @Groups({"trapezium-edit"})
     */
    private $max0;

    /**
     * @var \AppBundle\Entity\Criteria
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Criteria", inversedBy="trapezium")
     * @ORM\JoinColumn(name="criteria_id", referencedColumnName="id")
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
     * Get min0
     *
     * @return float
     */
    public function getMin0()
    {
        return $this->min0;
    }

    /**
     * Set min0
     *
     * @param float $min0
     *
     * @return Trapezium
     */
    public function setMin0($min0)
    {
        $this->min0 = $min0;

        return $this;
    }

    /**
     * Get min100
     *
     * @return float
     */
    public function getMin100()
    {
        return $this->min100;
    }

    /**
     * Set min100
     *
     * @param float $min100
     *
     * @return Trapezium
     */
    public function setMin100($min100)
    {
        $this->min100 = $min100;

        return $this;
    }

    /**
     * Get max100
     *
     * @return float
     */
    public function getMax100()
    {
        return $this->max100;
    }

    /**
     * Set max100
     *
     * @param float $max100
     *
     * @return Trapezium
     */
    public function setMax100($max100)
    {
        $this->max100 = $max100;

        return $this;
    }

    /**
     * Get max0
     *
     * @return float
     */
    public function getMax0()
    {
        return $this->max0;
    }

    /**
     * Set max0
     *
     * @param float $max0
     *
     * @return Trapezium
     */
    public function setMax0($max0)
    {
        $this->max0 = $max0;

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
     */
    public function setCriteria($criteria)
    {
        $this->criteria = $criteria;
    }

    /**
     * @param ExecutionContextInterface $context
     *
     * @Assert\Callback()
     */
    public function checkTrapezium(ExecutionContextInterface $context)
    {
        if ($this->min100 < $this->min0) {
            $context
                ->buildViolation('greater_than_var')
                ->setParameter('%var%', Constants::VARS['min0'])
                ->setParameter('%value%', $this->min0)
                ->atPath('min100')
                ->setInvalidValue($this->min100)
                ->addViolation();
        }
        if ($this->max100 < $this->min100) {
            $context
                ->buildViolation('greater_than_var')
                ->setParameter('%var%', Constants::VARS['min100'])
                ->setParameter('%value%', $this->min100)
                ->atPath('max100')
                ->setInvalidValue($this->max100)
                ->addViolation();
        }
        if ($this->max0 < $this->max100) {
            $context
                ->buildViolation('greater_than_var')
                ->setParameter('%var%', Constants::VARS['max100'])
                ->setParameter('%value%', $this->max100)
                ->atPath('max0')
                ->setInvalidValue($this->max0)
                ->addViolation();
        }
    }
}

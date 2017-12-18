<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;

/**
 * CriteriaType
 *
 * @ORM\Table(name="criteria_types")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CriteriaTypeRepository")
 */
class CriteriaType
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
     * @Groups({"evaluation-edit", "correction-edit", "criteria-charts"})
     *
     * @ORM\Column(name="type", type="string", length=15, unique=true)
     */
    private $type;


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
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return CriteriaType
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }
}


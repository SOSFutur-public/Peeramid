<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;

/**
 * SectionType
 *
 * @ORM\Table(name="section_types")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SectionTypeRepository")
 */
class SectionType
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
     * @Groups({"evaluation-edit", "assignment-edit", "correction-edit", "assignment-corrections"})
     *
     * @ORM\Column(name="label", type="string", length=64, nullable=true, unique=true)
     */
    private $label;


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
     * Get label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set label
     *
     * @param string $label
     *
     * @return SectionType
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }
}


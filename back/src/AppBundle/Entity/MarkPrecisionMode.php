<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;

/**
 * MarkPrecisionMode
 *
 * @ORM\Table(name="mark_precision_modes")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MarkPrecisionModeRepository")
 */
class MarkPrecisionMode
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
     * @Groups({"evaluation-edit"})
     *
     * @ORM\Column(name="mode", type="string", length=255)
     */
    private $mode;

    /**
     * @var string
     * @Groups({"evaluation-edit"})
     *
     * @ORM\Column(name="description", type="string", length=255)
     */
    private $description;


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
     * Get mode
     *
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * Set mode
     *
     * @param string $mode
     *
     * @return MarkPrecisionMode
     */
    public function setMode($mode)
    {
        $this->mode = $mode;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return MarkPrecisionMode
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }
}

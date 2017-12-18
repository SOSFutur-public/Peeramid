<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;

/**
 * MarkRoundMode
 *
 * @ORM\Table(name="mark_round_modes")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MarkRoundModeRepository")
 */
class MarkRoundMode
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
     * @return MarkRoundMode
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
     * @return MarkRoundMode
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }
}

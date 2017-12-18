<?php
/**
 * Created by PhpStorm.
 * User: Nicolas
 * Date: 29/08/2017
 * Time: 14:43
 */

namespace AppBundle\Entity;


use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ORM\Table(name="file_types")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\FileTypeRepository")
 */
class FileType
{
    /**
     * @var integer
     * @Groups({"id"})
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     * @Groups({"assignment-edit", "evaluation-edit"})
     *
     * @ORM\Column(type="string")
     */
    private $type;

    /**
     * @var string
     * @Groups({"assignment-edit"})
     *
     * @ORM\Column(type="string")
     */
    private $mime;

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param integer $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Get mime
     *
     * @return string
     */
    public function getMime()
    {
        return $this->mime;
    }

    /**
     * Set mime
     *
     * @param string $mime
     *
     * @return FileType
     */
    public function setMime($mime)
    {
        $this->mime = $mime;

        return $this;
    }
}

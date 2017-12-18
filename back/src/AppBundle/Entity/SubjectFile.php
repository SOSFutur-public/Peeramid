<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * SubjectFile
 *
 * @ORM\Table(name="evaluation_subject_files")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SubjectFileRepository")
 *
 * @UniqueEntity({"fileName", "evaluation"}, message="already_used")
 */
class SubjectFile
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
     * @Groups({"evaluation-edit", "assignment-edit"})
     *
     * @ORM\Column(name="fileName", type="string", length=255)
     */
    private $fileName;

    /**
     * @var UploadedFile
     * @Assert\File()
     */
    private $file;

    /**
     * @var Evaluation
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Evaluation")
     */
    private $evaluation;

    /**
     * SubjectFile constructor.
     * @param UploadedFile $file
     * @param Evaluation $evaluation
     */
    public function __construct(UploadedFile $file, Evaluation $evaluation)
    {
        $this->file = $file;
        $this->fileName = $file->getClientOriginalName();
        $this->evaluation = $evaluation;
    }

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
     * Get fileName
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * Set fileName
     *
     * @param string $fileName
     *
     * @return SubjectFile
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * Get evaluation
     *
     * @return Evaluation
     */
    public function getEvaluation()
    {
        return $this->evaluation;
    }

    /**
     * Set evaluation
     *
     * @param Evaluation $evaluation
     *
     * @return SubjectFile
     */
    public function setEvaluation(Evaluation $evaluation = null)
    {
        $this->evaluation = $evaluation;

        return $this;
    }

    /**
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param UploadedFile $file
     */
    public function setFile(UploadedFile $file)
    {
        $this->file = $file;
    }
}

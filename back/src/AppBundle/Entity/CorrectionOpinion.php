<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * CorrectionOpinion
 *
 * @ORM\Table(name="correction_opinions")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CorrectionOpinionRepository")
 */
class CorrectionOpinion
{
    /**
     * @var int
     * @Groups("id")
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     * @Groups({"correction-opinion-edit", "assignment-corrections", "evaluation-stats-criterias"})
     *
     * @ORM\Column(name="opinion", type="integer")
     * @Assert\Range(min="-1", max="1")
     */
    private $opinion = 0;

    /**
     * @var string
     * @Groups({"correction-opinion-edit", "assignment-corrections", "evaluation-stats-criterias"})
     *
     * @ORM\Column(name="comments", type="string", length=255, nullable=true)
     */
    private $comments;

    /**
     * @var \AppBundle\Entity\CorrectionCriteria
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\CorrectionCriteria", inversedBy="correction_opinion")
     */
    private $correction_criteria;


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
     * Get opinion
     *
     * @return int
     */
    public function getOpinion()
    {
        return $this->opinion;
    }

    /**
     * Set opinion
     *
     * @param integer $opinion
     *
     * @return CorrectionOpinion
     */
    public function setOpinion($opinion)
    {
        $this->opinion = $opinion;

        return $this;
    }

    /**
     * Get remarks
     *
     * @return string
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Set remarks
     *
     * @param string $comments
     *
     * @return CorrectionOpinion
     */
    public function setComments($comments)
    {
        $this->comments = $comments;

        return $this;
    }

    /**
     * @return CorrectionCriteria
     */
    public function getCorrectionCriteria()
    {
        return $this->correction_criteria;
    }

    /**
     * @param CorrectionCriteria $correction_criteria
     */
    public function setCorrectionCriteria($correction_criteria)
    {
        $this->correction_criteria = $correction_criteria;
    }
}


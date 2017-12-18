<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Group
 *
 * @ORM\Table(name="groups")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\GroupRepository")
 * @UniqueEntity("name", message="already_used")
 */
class Group
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
     * @Groups({"assignment-edit", "evaluation-edit", "evaluation-attribution", "lesson-edit",
     *     "admin-group-list", "admin-user-edit", "correction-edit",
     *     "evaluation-stats", "name", "teacher-lesson", "assignment-corrections", "correction-list"})
     *
     * @ORM\Column(name="name", type="string", length=191, unique=true)
     */
    private $name;

    /**
     * @var ArrayCollection
     * @Groups({"admin-group-list"})
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Lesson", mappedBy="groups")
     */
    private $lessons;

    /**
     * @var ArrayCollection
     * @Groups({"admin-group-list", "assignment-corrections", "assignment-correction-list"})
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\User", mappedBy="groups")
     */
    private $users;

    /**
     * @var
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Evaluation", inversedBy="groups")
     * @ORM\JoinTable(name="group_has_evaluation")
     */
    private $evaluations;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Assignment", mappedBy="group", cascade={"remove"})
     */
    private $assignments;

    /**
     * Group constructor.
     */
    public function __construct()
    {
        $this->lessons = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->evaluations = new ArrayCollection();
        $this->assignments = new ArrayCollection();
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
     * @return Group
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getLessons()
    {
        return $this->lessons;
    }

    /**
     * @param Lesson $lesson
     */
    public function addLesson(Lesson $lesson)
    {
        $this->lessons[] = $lesson;
        $lesson->addGroup($this);
    }

    /**
     * @param Lesson $lesson
     */
    public function removeLesson(Lesson $lesson)
    {
        $this->lessons->removeElement($lesson);
        $lesson->removeGroup($this);
    }

    /**
     * @return ArrayCollection
     */
    public function getUsers()
    {
        return $this->users;
    }

    public function addUser(User $user)
    {
        $this->users[] = $user;
        $user->addGroup($this);
    }

    public function removeUser(User $user)
    {
        $this->users->removeElement($user);
        $user->removeGroup($this);
    }

    /**
     * @return ArrayCollection
     */
    public function getEvaluations()
    {
        return $this->evaluations;
    }

    /**
     * @param Evaluation $evaluation
     */
    public function addEvaluation(Evaluation $evaluation)
    {
        $this->evaluations[] = $evaluation;
    }

    /**
     * @param Evaluation $evaluation
     */
    public function removeEvaluation(Evaluation $evaluation)
    {
        $this->evaluations->removeElement($evaluation);
    }

    /**
     * @return ArrayCollection
     */
    public function getAssignments()
    {
        return $this->assignments;
    }

    public function addAssignment(Assignment $assignment)
    {
        $this->assignments[] = $assignment;
        $assignment->setGroup($this);
    }

    public function removeAssignment(Assignment $assignment)
    {
        $this->assignments->removeElement($assignment);
    }
}
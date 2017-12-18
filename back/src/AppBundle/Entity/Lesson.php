<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Lesson
 *
 * @ORM\Table(name="lessons")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\LessonRepository")
 * @UniqueEntity("name", message="already_used")
 */
class Lesson
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
     * @Groups({"assignment-list", "lesson-list", "evaluation-edit", "evaluation-list",
     *     "admin-group-list", "admin-lesson-list", "admin-lesson-edit", "admin-user-edit", "user-lesson-info",
     *     "correction-list", "assignment-edit", "teacher-lesson"})
     *
     * @ORM\Column(name="name", type="string", length=100, nullable=true)
     * @Assert\NotBlank(message="not_blank")
     */
    private $name;

    /**
     * @var string
     * @Groups({"admin-lesson-list", "admin-lesson-edit", "user-lesson-info", "teacher-lesson"})
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var string
     * @Groups({"assignment-list", "lesson-list", "evaluation-edit", "evaluation-list",
     *     "admin-lesson-edit", "admin-lesson-list", "user-lesson-info",
     *     "correction-list", "teacher-lesson"})
     *
     * @ORM\Column(name="image", type="string", length=200, nullable=true)
     */
    private $image;

    /**
     * @var UploadedFile
     * @Assert\Image(mimeTypes={"image/png", "image/jpeg", "image/jpg", "image/gif"}, mimeTypesMessage="image")
     */
    private $imageFile;

    /**
     * @var \AppBundle\Entity\Category
     * @Groups({"assignment-list", "lesson-list", "evaluation-edit", "evaluation-list",
     *     "admin-lesson-list", "admin-lesson-edit", "user-lesson-info",
     *     "correction-list", "teacher-lesson"})
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Category")
     * @Assert\NotNull(message="not_null")
     */
    private $category;

    /**
     * @var
     * @Groups({"evaluation-edit", "teacher-lesson"})
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Group", inversedBy="lessons")
     * @ORM\JoinTable(name="lesson_has_group")
     */
    private $groups;

    /**
     * @var
     * @Groups({"evaluation-edit",
     *     "admin-lesson-list", "admin-lesson-edit", "user-lesson-info", "teacher-lesson"})
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\User", mappedBy="lessons")
     */
    private $users;

    /**
     * @var ArrayCollection
     * @Groups({"teacher-lesson"})
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Evaluation", mappedBy="lesson", orphanRemoval=true)
     */
    private $evaluations;

    /**
     * Lesson constructor.
     */
    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->groups = new ArrayCollection();
        $this->evaluations = new ArrayCollection();
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
     * @return Lesson
     */
    public function setName($name)
    {
        $this->name = $name;

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
     * @return Lesson
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get image
     *
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set image
     *
     * @param string $image
     *
     * @return Lesson
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param Category $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * @return ArrayCollection
     */
    public function getGroups()
    {
        return $this->groups;
    }

    public function addGroup(Group $group)
    {
        $this->groups[] = $group;
    }

    public function removeGroup(Group $group)
    {
        $this->groups->removeElement($group);
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
        $user->addLesson($this);
    }

    public function removeUser(User $user)
    {
        $this->users->removeElement($user);
        $user->removeLesson($this);
    }

    public function removeUsersByRole($role)
    {
        foreach ($this->users as $user) {
            if ($user->getRole()->getId() == $role) {
                $user->removeLesson($this);
            }
        }
    }

    /**
     * Add evaluation
     *
     * @param Evaluation $evaluation
     *
     * @return Lesson
     */
    public function addEvaluation(Evaluation $evaluation)
    {
        $this->evaluations[] = $evaluation;

        return $this;
    }

    /**
     * Remove evaluation
     *
     * @param Evaluation $evaluation
     */
    public function removeEvaluation(Evaluation $evaluation)
    {
        $this->evaluations->removeElement($evaluation);
    }

    /**
     * Get evaluations
     *
     * @return ArrayCollection
     */
    public function getEvaluations()
    {
        return $this->evaluations;
    }

    /**
     * @return UploadedFile
     */
    public function getImageFile()
    {
        return $this->imageFile;
    }

    /**
     * @param UploadedFile $imageFile
     */
    public function setImageFile(UploadedFile $imageFile)
    {
        $this->imageFile = $imageFile;
    }
}

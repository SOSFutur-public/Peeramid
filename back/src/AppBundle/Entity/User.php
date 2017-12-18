<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * User
 *
 * @ORM\Table(name="users")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserRepository")
 * @UniqueEntity("username", message="already_used")
 * @UniqueEntity("email", message="already_used")
 */
class User implements UserInterface
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
     * @Groups({"user-light", "assignment-edit", "evaluation-edit", "evaluation-attribution",
     *     "admin-user-list", "admin-lesson-edit", "admin-user-edit"})
     *
     * @ORM\Column(name="username", type="string", length=100, unique=true)
     * @Assert\NotBlank(message="not_blank")
     * @Assert\Regex("/^[0-9a-zA-ZàâäÀÂÄéèêëÉÈÊËîïÎÏùûüÛÙÜôöÔÖçÇ,.;:()_\x22\-\' ]+$/", message="not_valid")
     */
    private $username;

    /**
     * @var string
     * @Groups({"user-light", "assignment-edit", "evaluation-edit", "evaluation-attribution", "lesson-edit",
     *     "admin-user-list", "admin-lesson-edit", "admin-user-edit", "user-lesson-info", "teacher-lesson"})
     *
     * @ORM\Column(name="email", type="string", length=100, unique=true)
     *
     * @Assert\NotBlank(message="not_blank")
     */
    private $email;

    /**
     * @var string
     * @Groups({"user-light", "assignment-edit", "evaluation-edit", "evaluation-attribution", "lesson-edit",
     *     "admin-group-list", "admin-user-list", "admin-lesson-list", "admin-lesson-edit", "admin-user-edit",
     *     "user-lesson-info", "correction-edit",
     *     "evaluation-stats", "name", "teacher-lesson", "assignment-corrections", "correction-list"})
     *
     * @ORM\Column(name="last_name", type="string", length=100)
     * @Assert\NotBlank(message="not_blank")
     * @Assert\Regex("/^[a-zA-ZàâäÀÂÄéèêëÉÈÊËîïÎÏùûüÛÙÜôöÔÖçÇ\-\' ]+$/", message="not_valid")
     */
    private $last_name;

    /**
     * @var string
     * @Groups({"user-light", "assignment-edit", "evaluation-edit", "evaluation-attribution", "lesson-edit",
     *     "admin-group-list", "admin-user-list", "admin-lesson-list", "admin-lesson-edit", "admin-user-edit",
     *     "user-lesson-info", "correction-edit",
     *     "evaluation-stats", "name", "teacher-lesson", "assignment-corrections", "correction-list"})
     *
     * @ORM\Column(name="first_name", type="string", length=100)
     * @Assert\NotBlank(message="not_blank")
     * @Assert\Regex("/^[a-zA-ZàâäÀÂÄéèêëÉÈÊËîïÎÏùûüÛÙÜôöÔÖçÇ\-\' ]+$/", message="not_valid")
     */
    private $first_name;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=100, nullable=true)
     */
    private $password;

    /**
     * @var string
     * @Groups({"user-light", "evaluation-edit", "evaluation-attribution",
     *     "admin-user-edit"})
     *
     * @ORM\Column(name="image", type="string", length=100, nullable=true)
     */
    private $image;

    /**
     * @var UploadedFile
     * @Assert\Image(mimeTypes={"image/png", "image/jpeg", "image/jpg", "image/gif"}, mimeTypesMessage="image")
     */
    private $imageFile;

    /**
     * @var string
     * @Groups({"user-light", "assignment-edit", "evaluation-edit", "evaluation-attribution",
     *     "admin-user-edit"})
     *
     * @ORM\Column(name="code", type="string", length=45, nullable=true, unique=true)
     */
    private $code;

    /**
     * @var \AppBundle\Entity\Role
     * @Groups({"user-light",
     *     "admin-lesson-list", "admin-lesson-edit", "admin-user-edit", "user-lesson-info",
     *     "evaluation-edit", "evaluation-stats", "evaluation-attribution", "assignment-corrections",
     *     "assignment-correction-list", "teacher-lesson"})
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Role")
     * @Assert\NotNull(message="not_null")
     */
    private $role;

    /**
     * @var ArrayCollection
     * @Groups({"admin-user-edit"})
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Lesson", inversedBy="users")
     * @ORM\JoinTable(name="user_has_lesson")
     */
    private $lessons;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Evaluation", inversedBy="users")
     * @ORM\JoinTable(name="user_has_evaluation")
     */
    private $evaluations;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Evaluation", mappedBy="teacher", orphanRemoval=true)
     */
    private $teacher_evaluations;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Assignment", mappedBy="user", orphanRemoval=true)
     */
    private $assignments;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Correction", mappedBy="user", orphanRemoval=true)
     */
    private $corrections;

    /**
     * @var ArrayCollection
     * @Groups({"admin-user-edit"})
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Group", inversedBy="users")
     * @ORM\JoinTable(name="user_has_group")
     */
    private $groups;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\ConnectionLog", mappedBy="user", orphanRemoval=true)
     */
    private $connection_logs;

    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->role = 1;
        $this->lessons = new ArrayCollection();
        $this->evaluations = new ArrayCollection();
        $this->teacher_evaluations = new ArrayCollection();
        $this->groups = new ArrayCollection();
        $this->connection_logs = new ArrayCollection();
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
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set username
     *
     * @param string $username
     *
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * Set name
     *
     * @param string $last_name
     *
     * @return User
     */
    public function setLastName($last_name)
    {
        $this->last_name = mb_strtoupper($last_name);

        return $this;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * @param string $first_name
     */
    public function setFirstName($first_name)
    {
        $this->first_name = mb_convert_case($first_name, MB_CASE_TITLE);
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

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
     * @return User
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set code
     *
     * @param string $code
     *
     * @return User
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return Role
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @param Role $role
     */
    public function setRole($role)
    {
        $this->role = $role;
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
    }

    /**
     * @param Lesson $lesson
     */
    public function removeLesson(Lesson $lesson)
    {
        /** @var Evaluation $evaluation */
        foreach ($this->evaluations as $evaluation) {
            if ($evaluation->getLesson() === $lesson) {
                $this->removeEvaluation($evaluation);
            }
        }
        $this->lessons->removeElement($lesson);
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
     * @return ArrayCollection
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @param Group $group
     */
    public function addGroup(Group $group)
    {
        $this->groups[] = $group;
    }

    /**
     * @param Group $group
     */
    public function removeGroup(Group $group)
    {
        $this->groups->removeElement($group);
    }

    /**
     * Returns the roles granted to the user.
     *
     * <code>
     * public function getRoles()
     * {
     *     return array('ROLE_USER');
     * }
     * </code>
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     * @return array (Role|string) The user roles
     */
    public function getRoles()
    {
        switch ($this->role->getId()) {
            case 1:
                return array('ROLE_ADMIN');
            case 2:
                return array('ROLE_STUDENT');
            case 3:
                return array('ROLE_TEACHER');
        }
        return array();
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {

    }

    /**
     * Add teacherEvaluation
     *
     * @param Evaluation $teacherEvaluation
     *
     * @return User
     */
    public function addTeacherEvaluation(Evaluation $teacherEvaluation)
    {
        $this->teacher_evaluations[] = $teacherEvaluation;

        return $this;
    }

    /**
     * Remove teacherEvaluation
     *
     * @param Evaluation $teacherEvaluation
     */
    public function removeTeacherEvaluation(Evaluation $teacherEvaluation)
    {
        $this->teacher_evaluations->removeElement($teacherEvaluation);
    }

    /**
     * Get teacherEvaluations
     *
     * @return ArrayCollection
     */
    public function getTeacherEvaluations()
    {
        return $this->teacher_evaluations;
    }

    /**
     * Add connectionLog
     *
     * @param ConnectionLog $connectionLog
     *
     * @return User
     */
    public function addConnectionLog(ConnectionLog $connectionLog)
    {
        $this->connection_logs[] = $connectionLog;

        return $this;
    }

    /**
     * Remove connectionLog
     *
     * @param ConnectionLog $connectionLog
     */
    public function removeConnectionLog(ConnectionLog $connectionLog)
    {
        $this->connection_logs->removeElement($connectionLog);
    }

    /**
     * Get connectionLogs
     *
     * @return ArrayCollection
     */
    public function getConnectionLogs()
    {
        return $this->connection_logs;
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

    /**
     * Add assignment
     *
     * @param Assignment $assignment
     *
     * @return User
     */
    public function addAssignment(Assignment $assignment)
    {
        $this->assignments[] = $assignment;

        return $this;
    }

    /**
     * Remove assignment
     *
     * @param Assignment $assignment
     */
    public function removeAssignment(Assignment $assignment)
    {
        $this->assignments->removeElement($assignment);
    }

    /**
     * Get assignments
     *
     * @return ArrayCollection
     */
    public function getAssignments()
    {
        return $this->assignments;
    }

    /**
     * Add correction
     *
     * @param Correction $correction
     *
     * @return User
     */
    public function addCorrection(Correction $correction)
    {
        $this->corrections[] = $correction;

        return $this;
    }

    /**
     * Remove correction
     *
     * @param Correction $correction
     */
    public function removeCorrection(Correction $correction)
    {
        $this->corrections->removeElement($correction);
    }

    /**
     * Get corrections
     *
     * @return ArrayCollection
     */
    public function getCorrections()
    {
        return $this->corrections;
    }
}

<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PasswordToken
 *
 * @ORM\Table(name="password_tokens")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PasswordTokenRepository")
 */
class PasswordToken
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="token", type="string", length=191, unique=true)
     */
    private $token;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateCreation", type="datetime")
     */
    private $dateCreation;

    /**
     * @var \AppBundle\Entity\User
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\User")
     */
    private $user;

    public function __construct(User $user)
    {
        $this->dateCreation = new \DateTime();
        $this->user = $user;
        $alphabet = 'azertyuiopqsdfghjklmwxcvbnAZERTYUIOPQSDFGHJKLMWXCVBN0123456789';
        $alphaLength = strlen($alphabet) - 1;
        $tokenLength = rand(60, 90);
        $tokenArray = array();
        for ($i = 0; $i < $tokenLength; $i++) {
            $n = rand(0, $alphaLength);
            $tokenArray[] = $alphabet[$n];
        }
        $this->token = implode($tokenArray);
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
     * Get token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set token
     *
     * @param string $token
     *
     * @return PasswordToken
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get dateCreation
     *
     * @return \DateTime
     */
    public function getDateCreation()
    {
        return $this->dateCreation;
    }

    /**
     * Set dateCreation
     *
     * @param \DateTime $dateCreation
     *
     * @return PasswordToken
     */
    public function setDateCreation($dateCreation)
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }
}


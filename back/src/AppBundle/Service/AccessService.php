<?php


namespace AppBundle\Service;


use AppBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AccessService
{

    /** @var ContainerInterface */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function tryEntity(User $owner, array $allowedUsers)
    {
        return true;

        if (!in_array($owner, $allowedUsers)) {
            throw new AccessDeniedException("Opération interdite");
        }

        return true;
    }
}
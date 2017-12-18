<?php

namespace AppBundle\Service;

use AppBundle\Entity\ConnectionLog;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;

class LogService
{

    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Save a user connection to the database
     *
     * @param Request $request
     * @param User $user The connected user
     */
    public function log(Request $request, User $user)
    {
        $log = new ConnectionLog();
        $log->setIp($request->getClientIp());
        $log->setUser($user);

        $this->em->persist($log);
        $this->em->flush();
    }
}
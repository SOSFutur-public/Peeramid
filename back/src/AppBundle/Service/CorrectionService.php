<?php

namespace AppBundle\Service;

use AppBundle\Constants;
use AppBundle\Entity\Correction;
use AppBundle\Entity\Evaluation;
use DateTime;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Class CorrectionService
 * @package AppBundle\Service
 *
 */
class CorrectionService
{
    private $em;
    private $logger;
    private $container;

    /**
     * CorrectionService constructor.
     * @param LoggerInterface $logger
     * @param EntityManager $em
     * @param ContainerInterface $container
     */
    public function __construct(LoggerInterface $logger, EntityManager $em, ContainerInterface $container)
    {
        $this->em = $em;
        $this->logger = $logger;
        $this->container = $container;
    }

    /**
     * Check correction status before updating it (security/integrity reasons)
     * @param Correction $correction
     * @return bool
     */
    public function checkCorrectionContext(Correction $correction)
    {
        /** @var Evaluation $evaluation */
        $evaluation = $correction->getAssignment()->getEvaluation();

        // Reference correction (made by teacher) - no constraints
        if ($correction->getUser()) {
            if ($correction->getUser()->getRole()->getId() == Constants::ROLE_TEACHER) {
                return !$evaluation->getArchived();
            }
        }

        $now = new DateTime();
        // Update unauthorized because of dates
        return ($evaluation->getDateStartCorrection() < $now) && ($evaluation->getDateEndCorrection() > $now)
            && ($evaluation->getActiveCorrection()) && ($correction->getAssignment()->getDateSubmission());
    }
}
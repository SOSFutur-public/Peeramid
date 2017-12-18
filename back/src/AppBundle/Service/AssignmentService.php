<?php

namespace AppBundle\Service;

use AppBundle\Entity\Assignment;
use AppBundle\Entity\AssignmentSection;
use AppBundle\Entity\Evaluation;
use AppBundle\Entity\Section;
use DateTime;
use Doctrine\ORM\EntityManager;


/**
 * Class AssignmentService
 * @package AppBundle\Service
 *
 */
class AssignmentService
{
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Check if a "assignment" can be updated
     * @param Assignment $assignment
     * @return bool
     */
    public function checkAssignmentContext(Assignment $assignment)
    {
        /** @var Evaluation $evaluation */
        $evaluation = $this->em->getRepository('AppBundle:Evaluation')->find($assignment->getEvaluation()->getId());

        $now = new DateTime();
        $valid = ($evaluation->getDateStartAssignment() < $now) && ($evaluation->getDateEndAssignment() > $now) && ($evaluation->getActiveAssignment());
        return $valid;
    }

    /**
     * @param Assignment $assignment
     * @param Section $section
     */
    public function createAssignmentSection(Assignment $assignment, Section $section)
    {
        $assignmentSection = new AssignmentSection();
        $assignmentSection->setAssignment($assignment);
        $assignmentSection->setSection($section);
        $this->em->persist($assignmentSection);
        $this->em->flush();
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: Nicolas
 * Date: 19/09/2017
 * Time: 09:56
 */

namespace AppBundle\Service;


use AppBundle\Entity\CorrectionOpinion;
use AppBundle\Entity\Evaluation;
use DateTime;
use Doctrine\ORM\EntityManager;

class CorrectionOpinionService
{
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Check if a "correctionOpinion" can be updated
     * @param CorrectionOpinion $correctionOpinion
     * @return bool
     */
    public function checkDates(CorrectionOpinion $correctionOpinion)
    {
        /** @var Evaluation $evaluation */
        $evaluation = $this->em->getRepository('AppBundle:Evaluation')->find(
            $correctionOpinion
                ->getCorrectionCriteria()
                ->getCorrectionSection()
                ->getCorrection()
                ->getAssignment()
                ->getEvaluation()
                ->getId()
        );

        $now = new DateTime();
        $valid = ($evaluation->getDateEndCorrection() < $now) && ($evaluation->getDateEndOpinion() > $now);
        return $valid;
    }
}
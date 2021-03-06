<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Evaluation;
use AppBundle\Entity\Group;
use AppBundle\Entity\User;
use Datetime;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * CorrectionRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CorrectionRepository extends EntityRepository
{
    function findByEvaluation($evaluationId)
    {
        $qb = $this->createQueryBuilder('c');
        $qb->select('c')
            ->innerJoin('c.assignment', 'd')
            ->where('d.evaluation = :evaluationId')
            ->setParameters(array('evaluationId' => $evaluationId));

        $query = $qb->getQuery();
        $results = $query->getResult();
        return $results;
    }

    /**
     * Delete all corrections of an evaluation
     * @param Evaluation $evaluation
     */
    public function deleteByEvaluation(Evaluation $evaluation)
    {
        $subSelect = $this->_em->createQueryBuilder()
            ->select('d')
            ->from('AppBundle\Entity\Assignment', 'd')
            ->where('d.evaluation = :evaluation');

        $qb = $this->createQueryBuilder('c');
        $qb->delete();
        $qb->where($qb->expr()->in('c.assignment', $subSelect->getDQL()));
        $qb->setParameter('evaluation', $evaluation);
        $qb->getQuery()->execute();
    }

    public function findCorrectionsByUser($userId, $finished, $individual)
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->createQueryBuilder('c');
        $queryBuilder
            ->select('c')
            ->innerJoin('c.assignment', 'a')
            ->innerJoin('a.evaluation', 'e')
            ->andWhere('e.activeCorrection = true')
            ->andWhere('a.dateSubmission IS NOT NULL')
            ->andWhere(':now > e.dateStartCorrection');
        if ($individual) {
            $queryBuilder
                ->innerJoin('c.user', 'u')
                ->andWhere('u.id = :id')
                ->andWhere('c.group is null');
        } else {
            $queryBuilder
                ->innerJoin('c.group', 'g')
                ->innerJoin('g.users', 'u', 'WITH', 'u.id = :id')
                ->andWhere('c.user is null');
        }
        if ($finished) {
            $queryBuilder->andWhere(':now > e.dateEndCorrection');
        } else {
            $queryBuilder->andWhere(':now BETWEEN e.dateStartCorrection AND e.dateEndCorrection');
        }
        $params = array();
        $params['id'] = $userId;
        $params['now'] = new Datetime();
        $queryBuilder->orderBy('c.dateSubmission', 'ASC');
        $queryBuilder->setParameters($params);
        $query = $queryBuilder->getQuery();
        $results = $query->getResult();
        return $results;
    }

    public function findByUserAndLesson($userId, $lessonId, $individual)
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->createQueryBuilder('c');
        $queryBuilder
            ->select('c')
            ->innerJoin('c.assignment', 'a')
            ->innerJoin('a.evaluation', 'e')
            ->andWhere('a.dateSubmission IS NOT NULL')
            ->andWhere('e.lesson = :lessonId')
            ->andWhere('e.activeCorrection = true')
            ->andWhere(':now BETWEEN e.dateStartCorrection AND e.dateEndCorrection');
        if ($individual) {
            $queryBuilder
                ->innerJoin('c.user', 'u')
                ->andWhere('u.id = :userId')
                ->andWhere('c.group is null');
        } else {
            $queryBuilder
                ->innerJoin('c.group', 'g')
                ->innerJoin('g.users', 'u', 'WITH', 'u.id = :userId')
                ->andWhere('c.user is null');
        }
        $params = array();
        $params['userId'] = $userId;
        $params['lessonId'] = $lessonId;
        $params['now'] = new Datetime();
        $queryBuilder->orderBy('c.dateSubmission', 'ASC');
        $queryBuilder->setParameters($params);
        $query = $queryBuilder->getQuery();
        $results = $query->getResult();
        return $results;
    }

    /**
     * @param User $user
     * @param Evaluation $evaluation
     * @return array
     */
    public function findByUserAndEvaluation(User $user, Evaluation $evaluation)
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->createQueryBuilder('c');
        $queryBuilder->select('c')
            ->innerJoin('c.assignment', 'a')
            ->innerJoin('a.evaluation', 'e')
            ->andWhere('e = :evaluation')
            ->andWhere('c.user = :user')
            ->andWhere('c.dateSubmission IS NOT NULL')
            ->setParameter('evaluation', $evaluation)
            ->setParameter('user', $user);
        $query = $queryBuilder->getQuery();
        $results = $query->getResult();
        return $results;
    }

    /**
     * @param Group $group
     * @param Evaluation $evaluation
     * @return array
     */
    public function findByGroupAndEvaluation(Group $group, Evaluation $evaluation)
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->createQueryBuilder('c');
        $queryBuilder->select('c')
            ->innerJoin('c.assignment', 'a')
            ->innerJoin('a.evaluation', 'e')
            ->andWhere('e = :evaluation')
            ->andWhere('c.group = :group')
            ->andWhere('c.dateSubmission IS NOT NULL')
            ->setParameter('evaluation', $evaluation)
            ->setParameter('group', $group);
        $query = $queryBuilder->getQuery();
        $results = $query->getResult();
        return $results;
    }
}

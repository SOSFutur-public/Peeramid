<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Criteria;
use AppBundle\Entity\Group;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * CorrectionCriteriaRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CorrectionCriteriaRepository extends EntityRepository
{

    public function findByEvaluation($evaluationId)
    {
        $stmt = $this->getEntityManager()->getConnection()->prepare("
                SELECT 
                    assignment.id AS assignment_id, assignment.date_submission AS assignment_date_submission,
                    u.id AS assignment_user_id, u.username AS assignment_user_login, u.first_name AS assignment_user_prenom, u.last_name AS assignment_user_name,
                    g.id AS assignment_group_id, g.name AS assignment_group_name,
                    c.id AS correction_id, 
                    cu.id AS correction_user_id, cu.username AS correction_user_login, cu.first_name AS correction_user_prenom, cu.last_name AS correction_user_name,
                    cg.id AS correction_group_id, cg.name AS correction_group_name,
                    cr.id AS criteria_id, cr.options, cr.type_id,
                    cc.id AS id, cc.mark AS mark                    
                FROM assignments assignment
                LEFT JOIN assignment_sections ds ON assignment.id = ds.assignment_id
                LEFT JOIN criterias cr ON cr.section_id = ds.section_id
                LEFT JOIN users u ON assignment.user_id = u.id
                LEFT JOIN groups g ON assignment.group_id = g.id
                LEFT JOIN corrections c ON assignment.id = c.assignment_id AND c.reference = 0
                LEFT JOIN users cu ON c.user_id = cu.id
                LEFT JOIN groups cg ON c.group_id = cg.id
                LEFT JOIN correction_sections cs ON c.id = cs.correction_id AND ds.id = cs.assignment_section_id
                LEFT JOIN correction_criterias cc ON cs.id = cc.correction_section_id AND cr.id = cc.criteria_id
                WHERE assignment.evaluation_id = " . $evaluationId . "
                ORDER BY u.last_name, u.first_name, g.name, cu.last_name, cu.first_name, cg.name, ds.section_id, cr.id");
        $stmt->execute([]);

        return $stmt->fetchAll();
    }

    /**
     * @param integer $evaluationId
     * @return array
     */
    public function findByEvaluationForExport($evaluationId)
    {
        $stmt = $this->getEntityManager()->getConnection()->prepare("
                SELECT 
                    d.id AS assignment_id, d.date_submission AS assignment_date_submission,
                    u.id AS assignment_user_id, u.username AS assignment_user_login, u.first_name AS assignment_user_prenom, u.last_name AS assignment_user_nom,
                    g.id AS assignment_group_id, g.name AS assignment_group_name,

                    c.id AS correction_id, 
                    cu.id AS correction_user_id, cu.username AS correction_user_login, cu.first_name AS correction_user_prenom, cu.last_name AS correction_user_nom,
                    cg.id AS correction_group_id, cg.name AS correction_group_name,

                    cr.id AS criteria_id, cr.options, cr.type_id,
                    cc.id AS id, cc.mark AS mark, cc.comments AS comments                
                FROM assignments d
                LEFT JOIN assignment_sections ds ON d.id = ds.assignment_id
                LEFT JOIN criterias cr ON cr.section_id = ds.section_id
                LEFT JOIN users u ON d.user_id = u.id
                LEFT JOIN groups g ON d.group_id = g.id
                LEFT JOIN corrections c ON d.id = c.assignment_id AND c.reference = 0
                LEFT JOIN users cu ON c.user_id = cu.id
                LEFT JOIN groups cg ON c.group_id = cg.id
                LEFT JOIN correction_sections cs ON c.id = cs.correction_id AND ds.id = cs.assignment_section_id
                LEFT JOIN correction_criterias cc ON cs.id = cc.correction_section_id AND cr.id = cc.criteria_id
                WHERE d.evaluation_id = " . $evaluationId . "
                ORDER BY u.last_name, u.first_name, g.name, cu.last_name, cu.first_name, cg.name, ds.section_id, cr.id");
        $stmt->execute([]);

        return $stmt->fetchAll();
    }


    /**
     * Find all feedback (comments) for a assignment section
     * @param integer $assignmentId
     * @param integer $sectionId
     * @param boolean $correctionReference
     * @return array
     */
    public function findFeedbackByAssignmentAndSection($assignmentId, $sectionId, $correctionReference)
    {

        // Teacher comments
        if ($correctionReference) {
            $clause = 'cr.showTeacherComments = true';
        } // Students comments
        else {
            $clause = 'cr.showStudentsComments = true';
        }

        // Get comments
        $qb = $this->createQueryBuilder('cc');
        $qb->select('cc.comments')
            ->innerJoin('cc.criteria', 'cr', 'WITH', $clause)
            ->innerJoin('cr.section', 's', 'WITH', 's.id = :sectionId')
            ->innerJoin('cc.correction_section', 'cs')
            ->innerJoin('cs.correction', 'c', 'WITH', 'c.reference = :correctionReference')
            ->innerJoin('c.assignment', 'd', 'WITH', 'd.id = :assignmentId')
            ->orderBy('cr.order', 'ASC')
            ->setParameters(array(
                'sectionId' => $sectionId,
                'assignmentId' => $assignmentId,
                'correctionReference' => $correctionReference
            ));
        $query = $qb->getQuery();
        $results = $query->getResult();

        return $results;
    }

    /**
     * Find all correction criterias for correction reference (teacher)
     * @param integer $evaluationId
     * @return array
     */
    public function findReferences($evaluationId)
    {
        $qb = $this->createQueryBuilder('cc');
        $qb->select('cc as correction_criteria', 'd.id as assignment_id')
            ->innerJoin('cc.correction_section', 'cs')
            ->innerJoin('cs.correction', 'c', 'WITH', 'c.reference = true')
            ->innerJoin('c.assignment', 'd', 'WITH', 'd.evaluation = :evaluationId')
            ->setParameters(array(
                'evaluationId' => $evaluationId
            ));
        $query = $qb->getQuery();
        $results = $query->getResult();

        return $results;
    }

    /**
     * @param User $user
     * @param Criteria $criteria
     * @return array
     */
    public function findByUserAndCriteria(User $user, Criteria $criteria)
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->createQueryBuilder('cc');
        $queryBuilder->select('cc')
            ->innerJoin('cc.correction_section', 'cs')
            ->innerJoin('cs.correction', 'c')
            ->andWhere('cc.criteria = :criteria')
            ->andWhere('c.user = :user')
            ->andWhere('c.dateSubmission IS NOT NULL')
            ->setParameter('criteria', $criteria)
            ->setParameter('user', $user);
        $query = $queryBuilder->getQuery();
        $results = $query->getResult();
        return $results;
    }

    /**
     * @param Group $group
     * @param Criteria $criteria
     * @return array
     */
    public function findByGroupAndCriteria(Group $group, Criteria $criteria)
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->createQueryBuilder('cc');
        $queryBuilder->select('cc')
            ->innerJoin('cc.correction_section', 'cs')
            ->innerJoin('cs.correction', 'c')
            ->andWhere('cc.criteria = :criteria')
            ->andWhere('c.group = :group')
            ->andWhere('c.dateSubmission IS NOT NULL')
            ->setParameter('criteria', $criteria)
            ->setParameter('group', $group);
        $query = $queryBuilder->getQuery();
        $results = $query->getResult();
        return $results;
    }
}

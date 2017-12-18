<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class FileTypeRepository extends EntityRepository
{
    /**
     * @return array
     */
    public function findImageTypes()
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->createQueryBuilder('f');
        $queryBuilder->select('f')
            ->where('f.type IN (:types)')
            ->setParameter('types', ['jpg', 'jpeg', 'png', 'gif']);
        $query = $queryBuilder->getQuery();
        $results = $query->getResult();
        return $results;
    }

    public function findCsvType()
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->createQueryBuilder('f');
        $queryBuilder->select('f')
            ->where('f.type IN (:types)')
            ->setParameter('types', ['csv', 'txt']);
        $query = $queryBuilder->getQuery();
        $results = $query->getResult();
        return $results;
    }
}

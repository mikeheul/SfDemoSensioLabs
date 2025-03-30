<?php

namespace App\Repository;

use App\Entity\Training;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Training>
 */
class TrainingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Training::class);
    }

    public function findCoursesNotInTraining(Training $training): array
    {
        $em = $this->getEntityManager();
        $sub = $em->createQueryBuilder();

        $qb = $sub;

        $qb->select('c')
            ->from('App\Entity\Course', 'c')
            ->leftJoin('c.trainings', 't')
            ->where('t.id = :id');
            
        $sub = $em->createQueryBuilder();

        $sub->select('co')
            ->from('App\Entity\Course', 'co')
            ->where($qb->expr()->notIn('co.id', $qb->getDQL()))
            ->setParameter('id', $training->getId());
        
        $query = $sub->getQuery();
        return $query->getResult();
    }
}

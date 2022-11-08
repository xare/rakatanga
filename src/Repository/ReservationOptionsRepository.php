<?php

namespace App\Repository;

use App\Entity\ReservationOptions;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ReservationOptions|null find($id, $lockMode = null, $lockVersion = null)
 * @method ReservationOptions|null findOneBy(array $criteria, array $orderBy = null)
 * @method ReservationOptions[]    findAll()
 * @method ReservationOptions[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReservationOptionsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReservationOptions::class);
    }

    // /**
    //  * @return ReservationOptions[] Returns an array of ReservationOptions objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ReservationOptions
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

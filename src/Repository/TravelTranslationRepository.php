<?php

namespace App\Repository;

use App\Entity\TravelTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TravelTranslation|null find($id, $lockMode = null, $lockVersion = null)
 * @method TravelTranslation|null findOneBy(array $criteria, array $orderBy = null)
 * @method TravelTranslation[]    findAll()
 * @method TravelTranslation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TravelTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TravelTranslation::class);
    }

    // /**
    //  * @return TravelTranslation[] Returns an array of TravelTranslation objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TravelTranslation
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function listAll2()
    {
        return $this->createQueryBuilder('tt')
        ->orderBy('tt.title', 'ASC')
            ->getQuery()
            ->getResult();
    }
}

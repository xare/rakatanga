<?php

namespace App\Repository;

use App\Entity\PopupsTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PopupsTranslation|null find($id, $lockMode = null, $lockVersion = null)
 * @method PopupsTranslation|null findOneBy(array $criteria, array $orderBy = null)
 * @method PopupsTranslation[]    findAll()
 * @method PopupsTranslation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PopupsTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PopupsTranslation::class);
    }

    // /**
    //  * @return PopupsTranslation[] Returns an array of PopupsTranslation objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PopupsTranslation
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

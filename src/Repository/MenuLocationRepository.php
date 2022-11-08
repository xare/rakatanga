<?php

namespace App\Repository;

use App\Entity\MenuLocation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MenuLocation|null find($id, $lockMode = null, $lockVersion = null)
 * @method MenuLocation|null findOneBy(array $criteria, array $orderBy = null)
 * @method MenuLocation[]    findAll()
 * @method MenuLocation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MenuLocationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MenuLocation::class);
    }

    // /**
    //  * @return MenuLocation[] Returns an array of MenuLocation objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?MenuLocation
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

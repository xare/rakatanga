<?php

namespace App\Repository;

use App\Entity\Infodocs;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Infodocs|null find($id, $lockMode = null, $lockVersion = null)
 * @method Infodocs|null findOneBy(array $criteria, array $orderBy = null)
 * @method Infodocs[]    findAll()
 * @method Infodocs[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InfodocsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Infodocs::class);
    }

    // /**
    //  * @return Infodocs[] Returns an array of Infodocs objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Infodocs
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

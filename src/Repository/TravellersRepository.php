<?php

namespace App\Repository;

use App\Entity\Travellers;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Travellers|null find($id, $lockMode = null, $lockVersion = null)
 * @method Travellers|null findOneBy(array $criteria, array $orderBy = null)
 * @method Travellers[]    findAll()
 * @method Travellers[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TravellersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Travellers::class);
    }

    // /**
    //  * @return Travellers[] Returns an array of Travellers objects
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
    public function findOneBySomeField($value): ?Travellers
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

}

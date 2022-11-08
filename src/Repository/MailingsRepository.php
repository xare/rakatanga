<?php

namespace App\Repository;

use App\Entity\Mailings;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Mailings|null find($id, $lockMode = null, $lockVersion = null)
 * @method Mailings|null findOneBy(array $criteria, array $orderBy = null)
 * @method Mailings[]    findAll()
 * @method Mailings[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MailingsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Mailings::class);
    }

    // /**
    //  * @return Mailings[] Returns an array of Mailings objects
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
    public function findOneBySomeField($value): ?Mailings
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    public function listAll()
    {
        return $this->createQueryBuilder('m')
            ->getQuery();
    }

    public function listMailingsByCategory($categoryName)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.category = :categoryName')
            ->setParameter('categoryName', $categoryName)
            ->getQuery();
    }
}

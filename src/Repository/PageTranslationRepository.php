<?php

namespace App\Repository;

use App\Entity\PageTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PageTranslation|null find($id, $lockMode = null, $lockVersion = null)
 * @method PageTranslation|null findOneBy(array $criteria, array $orderBy = null)
 * @method PageTranslation[]    findAll()
 * @method PageTranslation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PageTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PageTranslation::class);
    }

    // /**
    //  * @return PageTranslation[] Returns an array of PageTranslation objects
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
    public function findOneBySomeField($value): ?PageTranslation
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    public function findPage($page_id)
    {
        return $this->createQueryBuilder('p')
            ->select('p.page as page')
            ->andWhere('p.page_id = :val')
            ->setParameter('val', $page_id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}

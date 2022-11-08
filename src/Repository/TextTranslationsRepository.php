<?php

namespace App\Repository;

use App\Entity\TextTranslations;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TextTranslations|null find($id, $lockMode = null, $lockVersion = null)
 * @method TextTranslations|null findOneBy(array $criteria, array $orderBy = null)
 * @method TextTranslations[]    findAll()
 * @method TextTranslations[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TextTranslationsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TextTranslations::class);
    }

    // /**
    //  * @return TextTranslations[] Returns an array of TextTranslations objects
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
    public function findOneBySomeField($value): ?TextTranslations
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

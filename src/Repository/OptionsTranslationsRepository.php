<?php

namespace App\Repository;

use App\Entity\OptionsTranslations;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method OptionsTranslations|null find($id, $lockMode = null, $lockVersion = null)
 * @method OptionsTranslations|null findOneBy(array $criteria, array $orderBy = null)
 * @method OptionsTranslations[]    findAll()
 * @method OptionsTranslations[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OptionsTranslationsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OptionsTranslations::class);
    }

    // /**
    //  * @return OptionsTranslations[] Returns an array of OptionsTranslations objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?OptionsTranslations
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

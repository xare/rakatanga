<?php

namespace App\Repository;

use App\Entity\Texts;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Texts|null find($id, $lockMode = null, $lockVersion = null)
 * @method Texts|null findOneBy(array $criteria, array $orderBy = null)
 * @method Texts[]    findAll()
 * @method Texts[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TextsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Texts::class);
    }

    public function listIndex()
    {
        return $this->createQueryBuilder('t')
                    ->select(
                        't.id as id,
                        t.section as section,
                        t.acronym as acronym,
                        t.title as title,
                        t.text as text,
                        t.date as date,
                        l.name as lang')
                        ->leftJoin('t.lang', 'l')
            ->getQuery()
            ->getResult();
    }
    // /**
    //  * @return Texts[] Returns an array of Texts objects
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
    public function findOneBySomeField($value): ?Texts
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

<?php

namespace App\Repository;

use App\Entity\Codespromo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Codespromo|null find($id, $lockMode = null, $lockVersion = null)
 * @method Codespromo|null findOneBy(array $criteria, array $orderBy = null)
 * @method Codespromo[]    findAll()
 * @method Codespromo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CodespromoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Codespromo::class);
    }
    // /**
    //  * @return Codespromo[] Returns an array of Codespromo objects
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
    public function findOneBySomeField($value): ?Invoices
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function listAll()
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.dateAjout', 'DESC')
            ->getQuery();
    }
    public function listCodespromoByTerm($term) {
        return $this->createQueryBuilder('c')
        ->where('c.code LIKE :term')
        ->setParameter('term','%'.$term.'%')
        ->getQuery()
        ->getResult();
    }
}

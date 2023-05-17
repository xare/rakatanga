<?php

namespace App\Repository;

use App\Entity\Oldreservations;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Oldreservations|null find($id, $lockMode = null, $lockVersion = null)
 * @method Oldreservations|null findOneBy(array $criteria, array $orderBy = null)
 * @method Oldreservations[]    findAll()
 * @method Oldreservations[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OldreservationsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Oldreservations::class);
    }

    // /**
    //  * @return Oldreservations[] Returns an array of Oldreservations objects
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
    public function findOneBySomeField($value): ?Oldreservations
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function listIndex()
    {
        return $this->createQueryBuilder('o')
            ->orderBy('o.date_ajout', 'DESC')
            ->getQuery();
    }

    public function findCorruptedCharacters(string $find){
        return $this->createQueryBuilder('r')
        ->where('r.log LIKE :find OR r.commentaire LIKE :find')
        ->setParameter('find', '%' . $find . '%')
        ->getQuery()
        ->getResult();
    }

    public function findWithIdGreaterOrEqual(int $value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.id >= :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getResult()
        ;
    }
}

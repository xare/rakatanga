<?php

namespace App\Repository;

use App\Entity\Document;
use App\Entity\Reservation;
use App\Entity\ReservationData;
use App\Entity\Travellers;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Document|null find($id, $lockMode = null, $lockVersion = null)
 * @method Document|null findOneBy(array $criteria, array $orderBy = null)
 * @method Document[]    findAll()
 * @method Document[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DocumentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Document::class);
    }

    public function listAll() 
    {
        return $this->createQueryBuilder('d')
            ->getQuery();
    }
    // /**
    //  * @return Document[] Returns an array of Document objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Document
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function getDocumentsByReservationByUser($reservation){
        return $this->createQueryBuilder('d')
        /* ->innerJoin(Reservation::class, 'r' , join::WITH, 'r.id = d.reservation' ) */
        ->andWhere('d.reservation = :reservation')
        ->setParameter('reservation', $reservation)
        ->andWhere('d.user = :user')
        ->setParameter('user', $reservation->getUser())
        ->getQuery()
        ->getResult();
    }
    public function getDocumentsByReservationByTraveller($reservation,$traveller){
        return $this->createQueryBuilder('d')
        ->innerJoin(ReservationData::class, 'rd')
        ->innerJoin(Travellers::class,'tr', join::WITH, 'tr.id = rd.travellers')
        ->andWhere('rd.reservation = :reservation')
        ->setParameter('reservation', $reservation)
        ->andWhere('tr.id = :traveller')
        ->setParameter('traveller', $traveller)
        ->getQuery()
        ->getResult();
    }
}

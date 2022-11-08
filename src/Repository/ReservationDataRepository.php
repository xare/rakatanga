<?php

namespace App\Repository;

use App\Entity\Document;
use App\Entity\ReservationData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ReservationData|null find($id, $lockMode = null, $lockVersion = null)
 * @method ReservationData|null findOneBy(array $criteria, array $orderBy = null)
 * @method ReservationData[]    findAll()
 * @method ReservationData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReservationDataRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReservationData::class);
    }

    // /**
    //  * @return ReservationData[] Returns an array of ReservationData objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ReservationData
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function getReservationDataByUser( $reservation ) {
        return $this->createQueryBuilder('rd')
        ->andWhere('rd.reservation = :reservation')
        ->setParameter('reservation', $reservation)
        ->andWhere('rd.User = :user')
        ->setParameter('user',$reservation->getUser())
        ->getQuery()
        ->getResult();
    }

    public function getReservationDataByTraveller( $reservation, $traveller) {
        return $this->createQueryBuilder('rd')
        ->innerJoin(Document::class, 'd', 'd.rd=rd.d')
        ->andWhere('rd.reservation = :reservation')
        ->setParameter('reservation', $reservation)
        ->andWhere('rd.travellers = :traveller')
        ->setParameter('traveller',$traveller)
        ->getQuery()
        ->getResult();
    }
}

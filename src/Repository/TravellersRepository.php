<?php

namespace App\Repository;

use App\Entity\Reservation;
use App\Entity\Travellers;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
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
    public function findWithoutProxy($id)
    {
        return $this->createQueryBuilder('t')
            ->where('t.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
    public function listAll()
    {
        return $this->createQueryBuilder('t')
            ->getQuery();
    }

    public function listOtherTravellers(Travellers $traveller, Reservation $reservation){
        return $this->createQueryBuilder('t')
            ->innerJoin(Reservation::class, 'r', Join::WITH, 'r = t.reservation')
            ->andWhere('t.reservation = :reservation')
            ->setParameter('reservation', $reservation)
            ->andWhere('t.id != :traveller')
            ->setParameter('traveller', $traveller->getId())
            ->getQuery()
            ->getResult();
    }

    public function getTotalTravellers(){
        $qb = $this->createQueryBuilder('t');
        $query = $qb->select('COUNT(t.id) as totalTravellers')->getQuery();
        $result = $query->getSingleScalarResult();
        return $result ? (int) $result : 0;
    }

    public function listTravellersByTerm($term) {
        return $this->createQueryBuilder('t')
            ->where('t.nom LIKE :term')
            ->orWhere('t.prenom LIKE :term')
            ->orwhere('t.email LIKE :term')
            ->setParameter('term', '%'.$term.'%')
            ->getQuery()
            ->getResult();
    }

}

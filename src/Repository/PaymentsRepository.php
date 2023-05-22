<?php

namespace App\Repository;

use App\Entity\Dates;
use App\Entity\Payments;
use App\Entity\Reservation;
use App\Entity\Travel;
use App\Entity\TravelTranslation;
use App\Entity\User;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Payments|null find($id, $lockMode = null, $lockVersion = null)
 * @method Payments|null findOneBy(array $criteria, array $orderBy = null)
 * @method Payments[]    findAll()
 * @method Payments[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PaymentsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Payments::class);
    }

    // /**
    //  * @return Payments[] Returns an array of Payments objects
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
    public function findOneBySomeField($value): ?Payments
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function listAll()
    {
        return $this->createQueryBuilder('p')
            ->getQuery();
    }

    public function getTotalAmountForCurrentYear() {
        $currentYear = (new DateTime())->format('Y');
        $startDate = new DateTime("{$currentYear}-01-01 00:00:00");
        $endDate = new DateTime("{$currentYear}-12-31 23:59:59");

        $qb = $this->createQueryBuilder('p');
        $query = $qb->select('SUM(p.ammount) as totalAmmount')
                ->where('p.date_ajout >= :startDate')
                ->andWhere('p.date_ajout <= :endDate')
                ->setParameter('startDate', $startDate)
                ->setParameter('endDate', $endDate)
                ->getQuery();

        $result = $query->getSingleScalarResult();
        return $result ? (float) $result : 0;
    }

    public function listPaymentsByTerm($term) {
        return $this->createQueryBuilder('p')
            ->innerJoin( Reservation::class, 'r', Join::WITH,'r.id = p.reservation' )
            ->innerJoin( Dates::class, 'd', Join::WITH, 'd.id = r.date' )
            ->innerJoin( Travel::class, 't', Join::WITH, 't.id = d.travel' )
            ->innerJoin( TravelTranslation::class, 'tt', Join::WITH, 't.id = tt.travel' )
            ->leftJoin( User::class, 'u', Join::WITH, 'u.id = r.user' )
            ->where( 'tt.title LIKE :term' )
            ->orWhere( 'tt.intro LIKE :term' )
            ->orWhere( 'tt.content LIKE :term' )
            ->orWhere( 'u.nom LIKE :term' )
            ->orWhere( 'u.prenom LIKE :term' )
            ->orWhere( 'u.email LIKE :term' )
            ->setParameter( 'term', '%'.$term.'%' )
            ->getQuery()
            ->getResult();
    }
}

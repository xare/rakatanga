<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\CategoryTranslation;
use App\Entity\Dates;
use App\Entity\Lang;
use App\Entity\Reservation;
use App\Entity\Travel;
use App\Entity\Travellers;
use App\Entity\TravelTranslation;
use App\Entity\User;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Reservation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Reservation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Reservation[]    findAll()
 * @method Reservation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    // /**
    //  * @return Reservation[] Returns an array of Reservation objects
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
    public function findOneBySomeField($value): ?Reservation
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    public function showReservation($date, $user): ?Reservation
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.date = :val1')
            ->setParameter('val1', $date)
            ->andWhere('r.user = :val2')
            ->setParameter('val2', $user)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function listAll()
    {
        return $this->createQueryBuilder('r')
            ->orderBy('r.date_ajout','DESC')
            ->getQuery();
    }

    public function listIndex()
    {
        return $this->createQueryBuilder('r')
                    ->select(
                        'r.id as id,
                        u.nom as Apellidos,
                        u.prenom as Nombre,
                        u.email as Email,
                        t.main_title as Titulo,
                        r.date_ajout as FechaReserva,
                        r.date_paiement as FechaPago,
                        r.status as Status,
                        r.nbpilotes as Pilotos,
                        r.nbAccomp as Acompanantes
                        ')
                    ->innerJoin(Dates::class, 'd', Join::WITH, 'd.id = r.date')
                    ->innerJoin(Travel::class, 't', Join::WITH, 't.id = d.travel')
                    ->leftJoin(User::class, 'u', Join::WITH, 'u.id = r.user')
            ->getQuery()
            ->getResult();
    }

    public function listMyReservations($user, $locale)
    {
        return $this->createQueryBuilder('r')
        ->select('tt.title as title')
        ->innerJoin(User::class, 'u', Join::WITH, 'u.id = r.user')
        ->innerJoin(Travel::class, 't', Join::WITH, 't.id = r.date')
        ->innerJoin(TravelTranslation::class, 'tt', Join::WITH, 't.id = tt.travel')
        ->innerJoin(Lang::class, 'l', Join::class, 'l.id = tt.lang')
        ->innerJoin(Dates::class, 'd', Join::WITH, 't.id = d.travel')
        ->andWhere('user = :user')
        ->setParameter('user', $user)
        ->andWhere('l.iso_code = :locale')
        ->setParameter('locale', $locale)
        ->getQuery()
        ->getResult();
    }

    public function listReservationsByCategory($categoryName)
    {
        return $this->createQueryBuilder('r')
                ->innerJoin(Dates::class, 'd', Join::WITH, 'd.id = r.date')
                ->innerJoin(Travel::class, 't', Join::WITH, 't.id = d.travel')
                ->innerJoin(Category::class, 'c', Join::WITH, 'c.id= t.category')
                ->andWhere('c.name = :categoryName')
                ->setParameter('categoryName', $categoryName)
                ->getQuery();
    }

    public function getLatestReservation(){
        return $this->createQueryBuilder('r')
        ->orderBy('r.date_ajout', 'DESC')
        ->setMaxResults(1)
        ->getQuery()
        ->getOneOrNullResult();
    }

    public function getTotalReservationsForCurrentYear() {
        $currentYear = (new DateTime())->format('Y');
        $startDate = new DateTime("{$currentYear}-01-01 00:00:00");
        $endDate = new DateTime("{$currentYear}-12-31 23:59:59");

        $qb = $this->createQueryBuilder('r');
        $query = $qb->select('COUNT(r.id) as totalReservations')
                ->where('r.date_ajout >= :startDate')
                ->andWhere('r.date_ajout <= :endDate')
                ->setParameter('startDate', $startDate)
                ->setParameter('endDate', $endDate)
                ->getQuery();
        $result = $query->getSingleScalarResult();
        return $result ? (int) $result : 0;
    }

    public function listReservationsByTerm($term) {
        $query = $this->createQueryBuilder('r');
        $result = $query->innerJoin(Dates::class, 'd', Join::WITH, 'd.id = r.date')
        ->innerJoin(Travel::class, 't', Join::WITH, 't.id = d.travel')
        ->innerJoin(Category::class, 'c', Join::WITH, 'c.id= t.category')
        ->innerJoin(CategoryTranslation::class, 'ct', Join::WITH, 'c.id= ct.category')
        ->innerJoin(TravelTranslation::class, 'tt', Join::WITH, 't.id= tt.travel')
        ->leftJoin(User::class, 'u', Join::WITH, 'u.id = r.user')
        ->leftJoin(Travellers::class, 'tr', Join::WITH, 'tr.reservation = r.id')
        ->where(
            $query->expr()->like('ct.title', ':term')
        )
        ->orWhere(
            $query->expr()->like('tt.title', ':term')
        )
        ->orWhere(
            $query->expr()->like('u.nom', ':term')
        )
        ->orWhere(
            $query->expr()->like('u.prenom', ':term')
        )
        ->orWhere(
            $query->expr()->like('u.email', ':term')
        )
        ->orWhere(
            $query->expr()->like('tr.nom', ':term')
        )
        ->orWhere(
            $query->expr()->like('tr.prenom', ':term')
        )
        ->orWhere(
            $query->expr()->like('tr.email', ':term')
        )
        ->setParameter(':term', '%' . $term . '%')
        ->getQuery()
        ->getResult();
        return $result;
    }
}

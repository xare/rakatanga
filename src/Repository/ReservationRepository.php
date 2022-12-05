<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Dates;
use App\Entity\Lang;
use App\Entity\Reservation;
use App\Entity\Travel;
use App\Entity\TravelTranslation;
use App\Entity\User;
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
}

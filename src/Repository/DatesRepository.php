<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\CategoryTranslation;
use App\Entity\Dates;
use App\Entity\Lang;
use App\Entity\Travel;
use App\Entity\TravelTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Dates|null find($id, $lockMode = null, $lockVersion = null)
 * @method Dates|null findOneBy(array $criteria, array $orderBy = null)
 * @method Dates[]    findAll()
 * @method Dates[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DatesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Dates::class);
    }

    public function listIndex()
    {
        return $this->createQueryBuilder('d')
            ->orderBy('d.debut', 'DESC')
            ->getQuery();
    }

    public function showNextDates($travel): array
    {
        $qb = $this->createQueryBuilder('d');
        $q = $qb
            ->select('
                    d.id as id,
                    d.debut as debut,
                    d.fin as fin,
                    d.statut as statut,
                    d.prixPilote,
                    d.prixAccomp')
            ->innerJoin(Travel::class, 't', Join::WITH, 't.id = d.travel')
            ->where(
                $qb->expr()->gt('d.debut', 'NOW()')
            )
            ->andWhere('d.travel = :travel')
            ->setParameter('travel', $travel['id'])
            ->orderBy('d.debut', 'ASC')
            ->getQuery();

        return $q->getResult();
    }

    public function showNextDate($travel)
    {
        $qb = $this->createQueryBuilder('d');
        $q = $qb
            ->select('d.debut as debut, d.fin as fin')
            ->innerJoin(Travel::class, 't', Join::WITH, 't.id = d.travel')
            ->where(
                $qb->expr()->gt('d.debut', 'NOW()')
            )
            ->andWhere('d.travel = :travel')
            ->setParameter('travel', $travel['id'])
            ->orderBy('d.debut', 'DESC')
            ->getQuery();

        return $q->getSingleResult();
    }

    public function listNextDates()
    {
        $qb = $this->createQueryBuilder('d');
        $q = $qb->where(
            $qb->expr()->gt('d.debut', 'NOW()')
        )
            ->orderBy('d.debut', 'DESC')
            ->getQuery();

        return $q->getResult();
    }

    public function listNextDatesCalendarPage()
    {
        $qb = $this->createQueryBuilder('d');
        $q =
            $qb->select('
                d.id as id,
                d.debut as debut,
                d.statut as statut,
                tt.title as title,
                t.id as travelId,
                YEAR(d.debut) as yearDebut,
                MONTH(d.debut) as monthDebut
            ')
            ->innerJoin(Travel::class, 't', Join::WITH, 't.id=d.travel')
            ->innerJoin(TravelTranslation::class, 'tt', Join::WITH, 't.id=tt.travel')
            ->innerJoin(Lang::class, 'l', Join::WITH, 'l.id=tt.lang')
            ->innerJoin(Category::class, 'c', Join::WITH, 'c.id=t.category')
            ->where(
                $qb->expr()->gt('d.debut', 'NOW()')
            )
            ->andWhere('l.iso_code = :lang')
            ->setParameter('lang', 'es')
            /* ->groupBy('yearDebut, monthDebut') */
            ->orderBy('d.debut', 'ASC')
            ->getQuery();

        return $q->getResult();
    }

    public function showDatesByMonth($month, $year)
    {
        $qb = $this->createQueryBuilder('d');
        $q = $qb->select('
            t.id as id,
            t.main_title as title,
            t as travel,
            d.debut as debut,
            d.fin as fin,
            d.statut as statut
        ')
            ->innerJoin(Travel::class, 't', Join::WITH, 't.id = d.travel')
            ->innerJoin(Category::class, 'c', Join::WITH, 'c.id = t.category')
            /* ->innerJoin(CategoryTranslation::class, 'ct', Join::WITH, 'c.id = ct.category') */
            ->andWhere('MONTHNAME(d.debut) = :month')
            ->setParameter('month', $month)
            ->andWhere('YEAR(d.debut) = :year')
            ->setParameter('year', $year)
            ->andWhere('d.statut = :statut')
            ->setParameter('statut', 'abierto')
            ->andwhere(
                $qb->expr()->gt('d.debut', 'NOW()')
            )
            ->orderBy('d.debut', 'ASC')
            ->getQuery();

        return $q->getResult();
    }

    public function getDatesByYear(): array
    {
        $qb = $this->createQueryBuilder('d');
        $q = $qb
            ->select('YEAR(d.debut) as year')
            ->andWhere(
                $qb->expr()->gt('d.debut', 'NOW()')
            )
            ->groupBy('year')
            ->getQuery();

        return $q->getResult();
    }

    public function getMonthedDates($year): array
    {
        $qb = $this->createQueryBuilder('d');
        $q = $qb
            ->select('
            MONTHNAME(d.debut) as monthname')
            ->andWhere(
                $qb->expr()->gt('d.debut', 'NOW()')
            )
            ->andWhere('YEAR(d.debut) = :year')
            ->setParameter('year', $year)
            ->groupBy('monthname')
            ->orderBy('MONTH(d.debut)', 'ASC')
            ->getQuery();

        return $q->getResult();
    }

    public function findDate($dateString, $travel)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.debut = :dateString')
            ->setParameter('dateString', $dateString)
            ->andWhere('d.travel = :travel')
            ->setParameter('travel', $travel)
            ->getQuery()
            ->getSingleResult();
    }

    public function listDatesByCategory($categoryName)
    {
        return $this->createQueryBuilder('d')
            ->innerJoin(Travel::class, 't', Join::WITH, 't.id = d.travel')
            ->innerJoin(TravelTranslation::class, 'tt', Join::WITH, 't.id = tt.travel')
            ->innerJoin(Category::class, 'c', Join::WITH, 'c.id= t.category')
            ->andWhere('c.name = :categoryName')
            ->setParameter('categoryName', $categoryName)
            ->getQuery();
    }
}

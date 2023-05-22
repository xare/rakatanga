<?php

namespace App\Repository;

use App\Entity\Oldreservations;
use App\Entity\Inscriptions;
use App\Entity\Dates;
use App\Entity\Travel;
use App\Entity\TravelTranslation;
use App\Entity\Category;
use App\Entity\CategoryTranslation;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
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

    public function getTotalReservationsForCurrentYear() {
        $currentYear = (new DateTime())->format('Y');
        $startDate = new DateTime("{$currentYear}-01-01 00:00:00");
        $endDate = new DateTime("{$currentYear}-12-31 23:59:59");

        $qb = $this->createQueryBuilder('o');
        $query = $qb->select('COUNT(o.id) as totalReservations')
                ->where('o.date_ajout >= :startDate')
                ->andWhere('o.date_ajout <= :endDate')
                ->setParameter('startDate', $startDate)
                ->setParameter('endDate', $endDate)
                ->getQuery();
        $result = $query->getSingleScalarResult();
        return $result ? (int) $result : 0;
    }

    public function getTotalAmountForCurrentYear() {
        $currentYear = (new DateTime())->format('Y');
        $startDate = new DateTime("{$currentYear}-01-01 00:00:00");
        $endDate = new DateTime("{$currentYear}-12-31 23:59:59");

        $qb = $this->createQueryBuilder('o');
        $query = $qb->select('SUM(o.montant) as totalAmmount')
                ->where('o.date_ajout >= :startDate')
                ->andWhere('o.date_ajout <= :endDate')
                ->setParameter('startDate', $startDate)
                ->setParameter('endDate', $endDate)
                ->getQuery();

        $result = $query->getSingleScalarResult();
        return $result ? (float) $result : 0;
    }
    public function listReservationsByTerm($term) {
        $query = $this->createQueryBuilder('ol');
        $result = $query->innerJoin(Dates::class, 'd', Join::WITH, 'd.id = ol.dates')
        ->innerJoin(Travel::class, 't', Join::WITH, 't.id = d.travel')
        ->innerJoin(Category::class, 'c', Join::WITH, 'c.id= t.category')
        ->innerJoin(CategoryTranslation::class, 'ct', Join::WITH, 'c.id= ct.category')
        ->innerJoin(TravelTranslation::class, 'tt', Join::WITH, 't.id= tt.travel')
        ->leftJoin(Inscriptions::class, 'i', Join::WITH, 'i.id = ol.inscriptions')
        ->where(
            $query->expr()->like('ct.title', ':term')
        )
        ->orWhere(
            $query->expr()->like('tt.title', ':term')
        )
        ->orWhere(
            $query->expr()->like('i.nom', ':term')
        )
        ->orWhere(
            $query->expr()->like('i.prenom', ':term')
        )
        ->orWhere(
            $query->expr()->like('i.email', ':term')
            )
        ->setParameter(':term', '%' . $term . '%')
        ->getQuery()
        ->getResult();
        return $result;
    }
}

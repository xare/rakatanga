<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\CategoryTranslation;
use App\Entity\Lang;
use App\Entity\Options;
use App\Entity\OptionsTranslations;
use App\Entity\Reservation;
use App\Entity\ReservationOptions;
use App\Entity\Travel;
use App\Entity\TravelTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Options|null find($id, $lockMode = null, $lockVersion = null)
 * @method Options|null findOneBy(array $criteria, array $orderBy = null)
 * @method Options[]    findAll()
 * @method Options[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OptionsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Options::class);
    }

    // /**
    //  * @return Options[] Returns an array of Options objects
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
    public function findOneBySomeField($value): ?Options
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function listOptions($reservationId, $locale)
    {
        return $this->createQueryBuilder('o')
        ->select('ot.title as title')
            ->innerJoin(ReservationOptions::class, 'ro', Join::WITH, 'ro.option = o.id')
            ->innerJoin(Reservation::class, 'r', Join::WITH, 'r.id = ro.reservation')
            ->innerJoin(OptionsTranslations::class, 'ot', Join::WITH, 'ot.option = o.id')
            ->innerJoin(Lang::class, 'l', Join::WITH, 'l.id = ot.lang')
            ->andWhere('r.id = :reservation')
            ->setParameter('reservation', $reservationId)
            ->andWhere('l.iso_code = :locale')
            ->setParameter('locale', $locale)
            ->getQuery()
            ->getResult();
    }

    public function listAll()
    {
        return $this->createQueryBuilder('o')
            ->getQuery();
    }

    public function listOptionsByCategory($categoryName)
    {
        return $this->createQueryBuilder('o')
                ->innerJoin(Travel::class, 't', Join::WITH, 't.id = o.travel')
                ->innerJoin(TravelTranslation::class, 'tt', Join::WITH, 't.id = tt.travel')
                ->innerJoin(Category::class, 'c', Join::WITH, 'c.id= t.category')
                ->andWhere('c.name = :categoryName')
                ->setParameter('categoryName', $categoryName)
                ->getQuery();
    }

    public function listOptionsByTerm($term) {
        return $this->createQueryBuilder('o')
            ->innerJoin(OptionsTranslations::class, 'ot', Join::WITH, 'o.id = ot.option')
            ->innerJoin(Travel::class, 't', Join::WITH, 't.id = o.travel')
            ->innerJoin(TravelTranslation::class, 'tt', Join::WITH, 't.id = tt.travel')
            ->innerJoin(Category::class, 'c', Join::WITH, 'c.id= t.category')
            ->innerJoin(CategoryTranslation::class, 'ct', Join::WITH, 'c.id = ct.category')
            ->where('ct.title LIKE :term')
            ->orWhere('tt.title LIKE :term')
            ->orWhere('ot.title LIKE :term')
            ->orWhere('ot.intro LIKE :term')
            ->setParameter('term', '%'.$term.'%')
            ->getQuery();

    }
}

<?php

namespace App\Repository;

use App\Entity\Lang;
use App\Entity\Popups;
use App\Entity\PopupsTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Popups|null find($id, $lockMode = null, $lockVersion = null)
 * @method Popups|null findOneBy(array $criteria, array $orderBy = null)
 * @method Popups[]    findAll()
 * @method Popups[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PopupsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Popups::class);
    }

    // /**
    //  * @return Popups[] Returns an array of Popups objects
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
    public function findOneBySomeField($value): ?Popups
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function showPopup($locale)
    {
        $qb = $this->createQueryBuilder('p');
        $q = $qb
            ->select('
                pt.title as title,
                pt.content as content
                ')
            ->innerJoin(PopupsTranslation::class, 'pt', Join::WITH, 'p.id = pt.popup')
            ->innerJoin(Lang::class, 'l', Join::WITH, 'l.id = pt.lang')
            ->where(
                $qb->expr()->lt('p.date_start', 'NOW()')
            )
            ->andWhere(
                $qb->expr()->gt('p.date_end', 'NOW()')
            )
            ->andWhere('l.iso_code = :locale')
            ->setParameter('locale', $locale)
            ->orderBy('p.id', 'DESC')
            ->getQuery();
            return $q->getOneOrNullResult();
    }
}

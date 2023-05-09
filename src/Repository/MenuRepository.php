<?php

namespace App\Repository;

use App\Entity\Lang;
use App\Entity\Menu;
use App\Entity\MenuTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Menu|null find($id, $lockMode = null, $lockVersion = null)
 * @method Menu|null findOneBy(array $criteria, array $orderBy = null)
 * @method Menu[]    findAll()
 * @method Menu[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MenuRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Menu::class);
    }

    public function listAll()
    {
        return $this->createQueryBuilder('m')
            ->getQuery();
    }

    // /**
    //  * @return Menu[] Returns an array of Menu objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Menu
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findMenuByLocation($locale, $location)
    {
        return $this->createQueryBuilder('m')
                ->select(
                    'm.id as id,
                    mt.title as title, 
                    mt.slug as slug,
                    m.routeName as routeName')
            ->innerJoin('m.menuLocations', 'ml', 'm.ml = ml.m')
            ->innerJoin(MenuTranslation::class, 'mt', Join::WITH, 'mt.menu = m.id')
            ->innerJoin(Lang::class, 'l', join::WITH, 'l.id = mt.lang')
            ->andWhere('ml.name = :location')
            ->setParameter('location', $location)
            ->andWhere('l.iso_code = :locale')
            ->setParameter('locale', $locale)
            ->orderBy('m.position', 'ASC')
            ->getQuery()
            ->getResult();
    }
}

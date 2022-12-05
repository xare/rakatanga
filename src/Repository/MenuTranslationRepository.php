<?php

namespace App\Repository;

use App\Entity\MenuTranslation;
use App\Entity\Menu;
use App\Entity\Lang;
use App\Entity\Pages;
use App\Entity\PageTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr\Join;

/**
 * @method MenuTranslation|null find($id, $lockMode = null, $lockVersion = null)
 * @method MenuTranslation|null findOneBy(array $criteria, array $orderBy = null)
 * @method MenuTranslation[]    findAll()
 * @method MenuTranslation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MenuTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MenuTranslation::class);
    }

    // /**
    //  * @return MenuTranslation[] Returns an array of MenuTranslation objects
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
    public function findOneBySomeField($value): ?MenuTranslation
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    public function findMenu($slug) {
        
        return $this->createQueryBuilder('m')
                ->select('menuTable.id as menuItem')
                ->InnerJoin(
                    Menu::class, 
                    'menuTable', 
                    Join::WITH, 
                    'menuTable.id = m.menu')     
                ->andWhere('m.slug = :slug')
                ->setParameter('slug', $slug)
                ->getQuery()
                ->getResult();
    }
    public function findCorrespondingSlug($slug, $lang)
    {
        
        $menu = $this->findMenu($slug);

        return $this->createQueryBuilder('m')
            ->select('m.title as title','m.slug as slug' ,'l.name as name', 'l.iso_code as isocode')
            ->innerJoin(Lang::class, 'l', Join::WITH, 'l.id = m.lang')
            ->andWhere('m.menu = :menu')
            ->setParameter('menu', $menu)
            ->andWhere('l.iso_code != :lang')
            ->setParameter('lang', $lang)
            ->getQuery()
            ->getResult();
    }

    public function findCorrespondingRoute($slug, $lang)
    {
        
        $menu = $this->findMenu($slug);
        return $this->createQueryBuilder('m')
            ->select('m.title as title','m.slug as slug' ,'l.name as name', 'l.iso_code as isocode')
            ->innerJoin(Lang::class, 'l', Join::WITH, 'l.id = m.lang')
            ->andWhere('m.menu = :menu')
            ->setParameter('menu', $menu[0])
            ->andWhere('l.iso_code = :lang')
            ->setParameter('lang', $lang)
            ->getQuery()
            ->getSingleResult();
    }

    public function getCorrespondingPage($slug, $lang)
    {

        return $this->createQueryBuilder('mt')
            ->select('pt.title as title', 'pt.body as body')
            ->innerJoin(Menu::class, 'mn', Join::WITH, 'mn.id = mt.menu' )
            ->innerJoin(Pages::class, 'p', Join::WITH, 'p.id = mn.page')
            ->innerJoin(PageTranslation::class, 'pt', Join::WITH, 'p.id = pt')
            ->innerJoin(Lang::class, 'l', Join::WITH, 'l.id= mt.lang' )
            ->andWhere('mt.slug = :slug')
            ->setParameter('slug',$slug)
            ->andWhere('mt.lang = :lang')
            ->setParameter('lang', $lang)
            ->getQuery()
            ->getSingleResult();
    }

    
}

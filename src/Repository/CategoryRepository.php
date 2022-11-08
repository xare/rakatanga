<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\CategoryTranslation;
use App\Entity\Continents;
use App\Entity\TravelTranslation;
use App\Entity\Travel;
use App\Entity\Dates;
use App\Entity\Media;
use App\Entity\Lang;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Entity\CategoryTranslation as EntityCategoryTranslation;

/**
 * @method Category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category[]    findAll()
 * @method Category[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    public function listAll() 
    {
        return $this->createQueryBuilder('c')
            ->getQuery();
    }
    
    public function showByCategory ($category, $locale){
        return $this->createQueryBuilder('c')
            ->innerJoin(CategoryTranslation::class, 'ct', Join::WITH, 'c.id = ct.category')
            ->andWhere('ct.slug = :category')
            ->setParameter('category', $category)
            ->getQuery()
            ->getResult();
    }

    public function findCountriesByContinent ($continent, $locale){
        return $this->createQueryBuilder('c')
            ->select(
                'ct.title as title,
                c.name as name,
                l.iso_code as iso_code
                ')
            ->innerJoin(CategoryTranslation::class, 'ct', Join::WITH, 'c.id = ct.category')    
            ->innerJoin(Lang::class, 'l', Join::WITH, 'l.id = ct.lang')
            ->innerJoin(Continents::class, 'co', Join::WITH, 'co.id = c.continents')
            ->andWhere('c.continents = :continent')
            ->setParameter('continent',$continent)
            ->andWhere('l.iso_code = :locale')
            ->setParameter('locale', $locale)
            ->orderBy('ct.title', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }

    public function findCategoriesForIndex($locale = 'es', $continent)
    {
        return $this->createQueryBuilder('c')
             ->select('
                ct.title as title,
                m.path as image,
                tt.url as url,
                MIN(d.prixPilote) as price
                ')
            ->innerJoin(Travel::class, 't', Join::WITH, 'c.id = t.category')
            ->innerJoin(TravelTranslation::class, 'tt', Join::WITH, 't.id = tt.travel')
            ->innerJoin(Lang::class, 'l', Join::WITH, 'l.id = tt.lang')
            ->innerJoin(Dates::class, 'd', Join::WITH, 't.id = d.travel')
            ->innerJoin(Media::class ,'m', Join::WITH,'m.id = c.mainPhoto' )
            ->innerJoin(CategoryTranslation::class, 'ct', Join::WITH, 'c.id = ct.category AND l.id = ct.lang' )
            ->innerJoin(Continents::class, 'co', Join::WITH, 'co.id = c.continents')
            ->groupBy('c.name')
            ->andWhere('l.iso_code = :locale')
            ->setParameter('locale', $locale)
            ->andWhere('c.continents = :continent')
            ->setParameter('continent',$continent)
            ->andWhere('d.statut = :statut')
            ->setParameter('statut','open')
            ->orderBy('ct.title', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findOtherCategoriesForIndex($locale = 'es', $continent)
    {
        return $this->createQueryBuilder('c')
             ->select('
                ct.title as title,
                m.path as image,
                tt.url as url,
                MIN(d.prixPilote) as price
                ')
            ->innerJoin(Travel::class, 't', Join::WITH, 'c.id = t.category')
            ->innerJoin(TravelTranslation::class, 'tt', Join::WITH, 't.id = tt.travel')
            ->innerJoin(Lang::class, 'l', Join::WITH, 'l.id = tt.lang')
            ->innerJoin(Dates::class, 'd', Join::WITH, 't.id = d.travel')
            ->innerJoin(Media::class ,'m', Join::WITH,'m.id = c.mainPhoto' )
            ->innerJoin(CategoryTranslation::class, 'ct', Join::WITH, 'c.id = ct.category AND l.id = ct.lang' )
            ->innerJoin(Continents::class, 'co', Join::WITH, 'co.id = c.continents')
            ->groupBy('c.name')
            ->andWhere('l.iso_code = :val')
            ->setParameter('val', $locale)
            ->andWhere('c.continents <> :val2')
            ->setParameter('val2',$continent)
            /* ->andWhere('d.debut >= NOW()') */
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findAsiaCategoriesForIndex($locale = 'es')
    {
        return $this->createQueryBuilder('c')
             ->select('
                c.name as category,
                m.path as image,
                tt.url as url,
                MIN(d.prixPilote) as price
                ')
            ->innerJoin(Travel::class, 't', Join::WITH, 'c.id = t.category')
            ->innerJoin(TravelTranslation::class, 'tt', Join::WITH, 't.id = tt.travel')
            ->innerJoin(Lang::class, 'l', Join::WITH, 'l.id = tt.lang')
            ->innerJoin(Dates::class, 'd', Join::WITH, 't.id = d.travel')
            ->innerJoin(Media::class ,'m', Join::WITH,'m.id = c.mainPhoto' )
            ->groupBy('c.name')
            ->andWhere('l.iso_code = :val')
            ->setParameter('val', $locale)
            ->andWhere('d.debut >= NOW()')
            ->andWhere('c.name = :category')
            ->setParameter('category','as')
            ->orderBy('c.name', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    public function footerList($locale){
        return $this->createQueryBuilder('c')
                ->select('ct.title as title')
                ->innerJoin(CategoryTranslation::class, 'ct', Join::WITH, 'c.id = ct.category')
                ->innerJoin(Lang::class, 'l', Join::WITH, 'l.id = ct.lang')
                ->andWhere('l.iso_code = :locale')
                ->setParameter('locale', $locale)
                ->getQuery()
                ->getResult();
    }

    public function listCategoryByContinent($continentCode){
        return $this->createQueryBuilder('c')
                ->innerJoin(CategoryTranslation::class, 'ct', Join::WITH, 'c.id = ct.category')
                ->innerJoin(Continents::class, 'co', Join::WITH, 'co.id= c.continents')
                ->andWhere('co.code = :continentCode')
                ->setParameter('continentCode',$continentCode)
                ->getQuery();
    }
}

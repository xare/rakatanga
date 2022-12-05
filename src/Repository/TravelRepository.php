<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\CategoryTranslation;
use App\Entity\Dates;
use App\Entity\Lang;
use App\Entity\Media;
use App\Entity\Travel;
use App\Entity\TravelTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Travel|null find($id, $lockMode = null, $lockVersion = null)
 * @method Travel|null findOneBy(array $criteria, array $orderBy = null)
 * @method Travel[]    findAll()
 * @method Travel[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TravelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Travel::class);
    }

    // /**
    //  * @return Travel[] Returns an array of Travel objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Travel
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    /**
     * @return Travel[] Returns an array of Travel objects
     */
    public function findTravelsForIndex($value = 'es')
    {
        return $this->createQueryBuilder('t')
            ->select('
                c.name as category,
                m.path as image,
                tt.url as url,
                d.prixAccomp as price
                ')
            ->innerJoin(TravelTranslation::class, 'tt', Join::WITH, 't.id = tt.travel')
            ->innerJoin(Lang::class, 'l', Join::WITH, 'l.id = tt.lang')
            ->innerJoin(Category::class, 'c', Join::WITH, 'c.id = t.category')
            ->innerJoin(Dates::class, 'd', Join::WITH, 't.id = d.travel')
            ->innerJoin(Media::class, 'm', Join::WITH, 'm.id = c.mainPhoto')
            ->andWhere('l.iso_code = :val')
            ->setParameter('val', $value)
            ->andWhere('d.debut >= NOW()')
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }

    public function listAll()
    {
        return $this->createQueryBuilder('t')
            ->getQuery();
    }

    public function listAll2()
    {
        return $this->createQueryBuilder('t')
            ->getQuery()
            ->getResult();
    }

    public function showTravelFromTranslationSlug($url)
    {
        return $this->createQueryBuilder('t')
            ->select('
            t.id,
            t.km,
            t.status,
            t.duration,
            t.level, 
            tt.title,
            tt.summary,
            tt.intro,
            tt.description,
            tt.itinerary,
            tt.practical_info,
            m.path as mainPhoto
            ')
            ->leftJoin(Media::class, 'm', Join::WITH, 'm.id = t.mainPhoto')
            ->innerJoin(TravelTranslation::class, 'tt', Join::WITH, 't.id = tt.travel')
            ->andWhere('tt.url = :url')
            ->setParameter('url', $url)
            ->orderBy('t.id', 'ASC')
            // ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function showByCategory($category, $locale)
    {
        return $this->createQueryBuilder('t')
            ->select('
                t.id as id,
                t.status as status,
                tt.title as title,
                tt.url as url,
                t.duration as duration,
                m.path as mainPhoto,
                d.prixAccomp as price,
                t.level as level,
                tt.summary as summary
            ')
            ->innerJoin(TravelTranslation::class, 'tt', Join::WITH, 't.id = tt.travel')
            ->innerJoin(Lang::class, 'l', Join::WITH, 'l.id = tt.lang')
            ->innerJoin(Category::class, 'c', Join::WITH, 'c.id = t.category')
            ->leftJoin(Media::class, 'm', Join::WITH, 'm.id = t.mainPhoto')
            ->innerJoin(Dates::class, 'd', Join::WITH, 't.id = d.travel')
            /* ->innerJoin(CategoryTranslation::class, 'ct', Join::WITH, 'l.id = ct.lang AND c.id =ct.category') */
            ->andWhere('l.iso_code = :locale')
            ->setParameter('locale', $locale)
            ->andWhere('c.id = :category')
            ->setParameter('category', $category)
            ->andWhere('t.status = :status')
            ->setParameter('status', 'oui')
            ->groupBy('t.id')
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }

    public function listIndex()
    {
        $travels = $this->findAll();
        $travelArray = [];
        foreach ($travels as $travel) {
            $id = $travel->getId();
            // $travelArray[$id] = $travel;
            $travelArray[$id]['translations'] =
                $this->getEntityManager()
                ->createQueryBuilder()
                ->select('tt.title as title')
                ->from(TravelTranslation::class, 'tt')
                ->where('tt.travel = :travel')
                ->setParameter('travel', $travelArray[$id])
                ->getQuery()
                ->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);
        }

        return $travelArray;
    }

    public function footerList($locale)
    {
        return $this->createQueryBuilder('t')
            ->select('tt.title as title')
            ->innerJoin(TravelTranslation::class, 'tt', Join::WITH, 't.id = tt.travel')
            ->innerJoin(Lang::class, 'l', Join::WITH, 'l.id = tt.lang')
            ->andWhere('l.iso_code = :locale')
            ->setParameter('locale', $locale)
            ->getQuery()
            ->getResult();
    }

    public function listTravelByCategory($categoryName)
    {
        return $this->createQueryBuilder('t')
            ->innerJoin(TravelTranslation::class, 'tt', Join::WITH, 't.id = tt.travel')
            ->innerJoin(Category::class, 'c', Join::WITH, 'c.id= t.category')
            ->andWhere('c.name = :categoryName')
            ->setParameter('categoryName', $categoryName)
            ->getQuery();
    }
}

<?php

namespace App\Repository;

use App\Entity\Articles;
use App\Entity\Blog;
use App\Entity\Lang;
use App\Entity\Media;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Articles|null find($id, $lockMode = null, $lockVersion = null)
 * @method Articles|null findOneBy(array $criteria, array $orderBy = null)
 * @method Articles[]    findAll()
 * @method Articles[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticlesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Articles::class);
    }

    // /**
    //  * @return Articles[] Returns an array of Articles objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Articles
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function showArticleFromSlug($url)
    {
        return $this->createQueryBuilder('a')
            ->select('
            a.id,
            a.title,
            a.intro,
            a.body,
            a.publishedAt,
            m.path as mainPhoto
            ')
            ->leftJoin(Media::class, 'm', Join::WITH, 'm.id = a.mainPhoto')
            ->andWhere('a.slug = :url')
            ->setParameter('url', $url)
            ->orderBy('a.publishedAt', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function listByLang($locale) 
    {
        return $this->createQueryBuilder('a')
            ->innerJoin(Blog::class, 'b', Join::WITH, 'b.id = a.blog')
            ->innerJoin(Lang::class, 'l', Join::WITH, 'l.id = b.lang')
            ->andWhere('l.iso_code = :locale')
            ->setParameter('locale', $locale)
            ->orderBy('a.publishedAt','DESC')
            ->getQuery();
    }
    public function listAll() 
    {
        return $this->createQueryBuilder('a')
            ->orderBy('a.publishedAt','DESC')
            ->getQuery();
    }
   

}

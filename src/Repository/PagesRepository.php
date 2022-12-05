<?php

namespace App\Repository;

use App\Entity\Media;
use App\Entity\Pages;
use App\Entity\PageTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Pages|null find($id, $lockMode = null, $lockVersion = null)
 * @method Pages|null findOneBy(array $criteria, array $orderBy = null)
 * @method Pages[]    findAll()
 * @method Pages[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PagesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Pages::class);
    }

    // /**
    //  * @return Pages[] Returns an array of Pages objects
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
    public function findOneBySomeField($value): ?Pages
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function listAll()
    {
        return $this->createQueryBuilder('p')
            ->getQuery();
    }

    public function showPageFromTranslationSlug($url)
    {
        return $this->createQueryBuilder('p')
        ->select('
            p.id,
            pt.title,
            pt.body,
            pt.slug,
            m.path as mainPhoto
            ')
        ->leftJoin(Media::class, 'm', Join::WITH, 'm.id = p.mainPhoto')
        ->innerJoin(PageTranslation::class, 'pt', Join::WITH, 'p.id = pt.Page')
        ->andWhere('pt.slug = :url')
        ->setParameter('url', $url)
        ->orderBy('p.id', 'ASC')
        ->setMaxResults(1)
        ->getQuery()
        ->getOneOrNullResult();
    }
}

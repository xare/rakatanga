<?php

namespace App\Repository;

use App\Entity\Media;
use App\Entity\Travel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Media|null find($id, $lockMode = null, $lockVersion = null)
 * @method Media|null findOneBy(array $criteria, array $orderBy = null)
 * @method Media[]    findAll()
 * @method Media[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MediaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Media::class);
    }

    // /**
    //  * @return Media[] Returns an array of Media objects
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
    public function findOneBySomeField($value): ?Media
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function listAll()
    {
        return $this->createQueryBuilder('m')
            ->getQuery();
    }

    public function listIndex()
    {
        return $this->createQueryBuilder('m')
                    ->select(
                        'm.id as id, 
                        m.type as type,
                        m.name as name,
                        m.path as path,
                        m.filename as filename,
                        t.main_title as travel_title')
                        ->leftJoin('m.travel', 't')
            ->getQuery()
            ->getResult();
    }

    public function listTravelMedia($travelId)
    {
        return $this->createQueryBuilder('m')
                    ->select(
                        'm.id as id, 
                        m.type as type,
                        m.name as name,
                        m.path as path,
                        m.filename as filename,
                        t.main_title as travel_title')
                    ->innerJoin(Travel::class, 't')
                    ->andWhere('t.id = :travel_id')
                    ->setParameter('travel_id', $travelId)
            ->getQuery()
            ->getResult();
    }
}

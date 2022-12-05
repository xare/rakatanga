<?php

namespace App\Repository;

use App\Entity\Logs;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Logs|null find($id, $lockMode = null, $lockVersion = null)
 * @method Logs|null findOneBy(array $criteria, array $orderBy = null)
 * @method Logs[]    findAll()
 * @method Logs[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LogsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Logs::class);
    }

    public function listAll()
    {
        return $this->createQueryBuilder('l')
            ->orderBy('l.date', 'DESC')
            ->getQuery();
    }

    public function listIndex()
    {
        return $this->createQueryBuilder('l')
            ->select(
                'l.id as id, 
                        l.entity as entity,
                        l.action as action,
                        l.content as content,
                        l.data as data'
            )
            ->getQuery()
            ->getResult();
    }

    public function listLogsByEntity($entity)
    {
        return $this->createQueryBuilder('l')
            /* ->innerJoin( Travel::class, 't', Join::WITH, 't.id = d.travel' )
                ->innerJoin( TravelTranslation::class, 'tt', Join::WITH, 't.id = tt.travel')
                ->innerJoin( Category::class , 'c', Join::WITH, 'c.id= t.category') */
            ->andWhere('l.entity = :entity')
            ->setParameter('entity', $entity)
            ->getQuery();
    }
}

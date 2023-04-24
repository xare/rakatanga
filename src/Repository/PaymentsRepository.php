<?php

namespace App\Repository;

use App\Entity\Payments;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Payments|null find($id, $lockMode = null, $lockVersion = null)
 * @method Payments|null findOneBy(array $criteria, array $orderBy = null)
 * @method Payments[]    findAll()
 * @method Payments[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PaymentsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Payments::class);
    }

    // /**
    //  * @return Payments[] Returns an array of Payments objects
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
    public function findOneBySomeField($value): ?Payments
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

    public function getTotalAmountForCurrentYear() {
        $currentYear = (new DateTime())->format('Y');
        $startDate = new DateTime("{$currentYear}-01-01 00:00:00");
        $endDate = new DateTime("{$currentYear}-12-31 23:59:59");

        $qb = $this->createQueryBuilder('p');
        $query = $qb->select('SUM(p.ammount) as totalAmmount')
                ->where('p.date_ajout >= :startDate')
                ->andWhere('p.date_ajout <= :endDate')
                ->setParameter('startDate', $startDate)
                ->setParameter('endDate', $endDate)
                ->getQuery();

        $result = $query->getSingleScalarResult();
        return $result ? (float) $result : 0;
    }
}

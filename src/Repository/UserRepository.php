<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    // /**
    //  * @return User[] Returns an array of User objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function listAll()
    {
        return $this->createQueryBuilder('u')
            ->getQuery();
    }

    public function searchUser($term, $field)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.'.$field.' LIKE :term')
            ->setParameter('term', '%'.$term.'%')
            ->getQuery()
            ->getResult();
    }

    public function listIndex()
    {
        return $this->createQueryBuilder('u')
                    ->select(
                        'u.id as id, 
                        u.email as email,
                        u.prenom as prenom,
                        u.nom as nom,
                        u.telephone as telephone')
            ->getQuery()
            ->getResult();
    }

    public function getTotalUsers(){
        $qb = $this->createQueryBuilder('u');
        $query = $qb->select('COUNT(u.id) as totalUsers')->getQuery();
        $result = $query->getSingleScalarResult();
        return $result ? (int) $result : 0;
    }
    public function listUsersByTerm($term) {
        return $this->createQueryBuilder('u')
            ->where('u.nom LIKE :term')
            ->orWhere('u.prenom LIKE :term')
            ->orwhere('u.email LIKE :term')
            ->setParameter('term', '%'.$term.'%')
            ->getQuery()
            ->getResult();
    }

}

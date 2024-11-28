<?php

namespace App\Repository;

use App\Entity\UserPost;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserPost>
 *
 * @method UserPost|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserPost|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserPost[]    findAll()
 * @method UserPost[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserPostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserPost::class);
    }

//    /**
//     * @return UserPost[] Returns an array of UserPost objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?UserPost
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}

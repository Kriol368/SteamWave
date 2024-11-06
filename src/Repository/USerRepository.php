<?php

namespace App\Repository;

use App\Entity\USer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<USer>
 *
 * @method USer|null find($id, $lockMode = null, $lockVersion = null)
 * @method USer|null findOneBy(array $criteria, array $orderBy = null)
 * @method USer[]    findAll()
 * @method USer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class USerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, USer::class);
    }

//    /**
//     * @return USer[] Returns an array of USer objects
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

//    public function findOneBySomeField($value): ?USer
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}

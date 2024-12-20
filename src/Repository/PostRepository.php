<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Post>
 *
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }
    public function findByContent($text): array // pa buscar por trozo de texto dentro del content
    {
        $qb = $this->createQueryBuilder('post')
            ->andwhere('post.content LIKE :text')
            ->setParameter('text', '%' . $text. '%')
            ->getQuery();
        return $qb->execute();
    }

    public function findPostsByUsers(array $userIds): array
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.postUser', 'u')
            ->andWhere('u.id IN (:userIds)')
            ->setParameter('userIds', $userIds)
            ->orderBy('p.publishedAt', 'DESC') 
            ->getQuery()
            ->getResult();
    }
//    /**
//     * @return Post[] Returns an array of Post objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Post
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}

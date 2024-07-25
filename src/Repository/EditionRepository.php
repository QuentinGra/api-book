<?php

namespace App\Repository;

use App\Entity\Edition;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<Edition>
 */
class EditionRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly PaginatorInterface $pagination,
    ) {
        parent::__construct($registry, Edition::class);
    }

    public function findAllWithPagination(int $page, ?int $limit): PaginationInterface
    {
        $query = $this->createQueryBuilder('u')
            ->orderBy('u.createdAt', 'ASC');

        return $this->pagination->paginate(
            $query->getQuery(),
            $page,
            $limit
        );
    }

    //    /**
    //     * @return Edition[] Returns an array of Edition objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('e.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Edition
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}

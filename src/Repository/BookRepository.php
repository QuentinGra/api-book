<?php

namespace App\Repository;

use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<Book>
 */
class BookRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly PaginatorInterface $pagination,
    ) {
        parent::__construct($registry, Book::class);
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

    public function findBooksByReadingList(int $id, ?string $status = null): iterable
    {
        $query = $this->createQueryBuilder('b')
            ->select('b', 'rlb')
            ->join('b.readingListBooks', 'rlb')
            ->join('rlb.readingList', 'rl')
            ->andWhere('rl.id = :id')
            ->setParameter('id', $id);
        if ($status) {
            $query->andWhere('rlb.status = :status')
                ->setParameter('status', $status);
        }

        return $query->getQuery()->getResult();
    }

    //    /**
    //     * @return Book[] Returns an array of Book objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('b.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Book
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}

<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Product;
use App\Enum\ProductStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function existsActiveBySku(string $sku): bool
    {
        return $this->createQueryBuilder('product')
                ->andWhere('product.sku = :sku')
                ->andWhere('product.deletedAt IS NULL')
                ->setParameter('sku', $sku)
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult() !== null;
    }

    public function findActiveById(int $id): ?Product
    {
        return $this->createQueryBuilder('product')
            ->andWhere('product.id = :id')
            ->andWhere('product.deletedAt IS NULL')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function existsActiveBySkuExcludingId(string $sku, int $excludedId): bool
    {
        return $this->createQueryBuilder('product')
                ->andWhere('product.sku = :sku')
                ->andWhere('product.deletedAt IS NULL')
                ->andWhere('product.id != :excludedId')
                ->setParameter('sku', $sku)
                ->setParameter('excludedId', $excludedId)
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult() !== null;
    }

    public function findPaginated(?string $status, int $page, int $limit): array
    {
        $queryBuilder = $this->createQueryBuilder('product')
            ->andWhere('product.deletedAt IS NULL')
            ->orderBy('product.id', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        if ($status !== null) {
            $queryBuilder->andWhere('product.status = :status')
                ->setParameter('status', ProductStatus::from($status));
        }

        return $queryBuilder->getQuery()->getResult();
    }

    public function countActive(?string $status): int
    {
        $queryBuilder = $this->createQueryBuilder('product')
            ->select('COUNT(product.id)')
            ->andWhere('product.deletedAt IS NULL');

        if ($status !== null) {
            $queryBuilder->andWhere('product.status = :status')
                ->setParameter('status', ProductStatus::from($status));
        }

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }
}

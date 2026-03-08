<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Product;
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


}

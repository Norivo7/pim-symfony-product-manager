<?php

declare(strict_types=1);

namespace App\Application\Product;

use App\Entity\Product;
use App\Enum\Currency;
use App\Enum\ProductStatus;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class CreateProduct
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ProductRepository $productRepository,
    ) {
    }

    public function handle(
        string $name,
        string $sku,
        string $price,
        string $currency,
        string $status,
    ): Product {
        if ($this->productRepository->existsActiveBySku($sku)) {
            throw new \DomainException('Active product with given SKU already exists.');
        }

        $product = new Product(
            $name,
            $sku,
            $price,
            Currency::from($currency),
            ProductStatus::from($status),
        );

        $this->entityManager->persist($product);
        $this->entityManager->flush();

        return $product;
    }
}

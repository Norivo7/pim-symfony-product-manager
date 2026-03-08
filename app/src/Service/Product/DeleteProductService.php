<?php

declare(strict_types=1);

namespace App\Service\Product;

use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DeleteProductService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ProductRepository $productRepository,
    ) {
    }

    public function handle(int $id): void
    {
        $product = $this->productRepository->findActiveById($id);

        if ($product === null) {
            throw new \DomainException('Product not found.');
        }

        $product->softDelete();

        $this->entityManager->flush();
    }
}

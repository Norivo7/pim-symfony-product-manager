<?php

declare(strict_types=1);

namespace App\Service\Product;

use App\Entity\Product;
use App\Enum\Currency;
use App\Enum\ProductStatus;
use App\Event\ProductPriceChanged;
use App\Repository\ProductRepository;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final readonly class UpdateProductService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ProductRepository $productRepository,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    /**
     * @throws OptimisticLockException
     */
    public function handle(
        int $id,
        string $name,
        string $sku,
        string $price,
        string $currency,
        string $status,
        int $version,
    ): Product
    {
        $product = $this->productRepository->findActiveById($id);

        if ($product === null) {
            throw new \DomainException('Product not found.');
        }

        $this->entityManager->lock($product, LockMode::OPTIMISTIC, $version);

        if ($this->productRepository->existsActiveBySkuExcludingId($sku, $id)) {
            throw new \DomainException('SKU is already used by another active product.');
        }

        $oldPrice = $product->getPrice();

        $product->updateDetails(
            $name,
            $sku,
            Currency::from($currency),
            ProductStatus::from($status),
        );

        $priceChanged = $product->changePrice($price);

        $this->entityManager->flush();

        if ($priceChanged) {
            $this->eventDispatcher->dispatch(
                new ProductPriceChanged(
                    $product->getId(),
                    $oldPrice,
                    $price,
                    new \DateTimeImmutable(),
                )
            );
        }

        return $product;
    }
}

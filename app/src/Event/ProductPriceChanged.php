<?php

declare(strict_types=1);

namespace App\Event;

final readonly class ProductPriceChanged
{
    public function __construct(
        public int $productId,
        public string $oldPrice,
        public string $newPrice,
        public \DateTimeImmutable $changedAt,
    ) {
    }

}

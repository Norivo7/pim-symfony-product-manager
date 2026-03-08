<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Event\ProductPriceChanged;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
final readonly class ProductPriceChangedListener
{
    public function __construct(
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(ProductPriceChanged $event): void
    {
            $this->logger->info('Product price changed', [
                'productId' => $event->productId,
                'oldPrice' => $event->oldPrice,
                'newPrice' => $event->newPrice,
                'changedAt' => $event->changedAt->format(\DateTimeInterface::ATOM),
            ]);
    }
}

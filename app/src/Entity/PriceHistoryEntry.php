<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PriceHistoryEntryRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PriceHistoryEntryRepository::class)]
#[ORM\Table(name: 'price_history_entries')]
class PriceHistoryEntry
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'priceHistoryEntries')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Product $product;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private string $oldPrice;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private string $newPrice;

    #[ORM\Column]
    private \DateTimeImmutable $changedAt;

    public function __construct(
        Product $product,
        string $oldPrice,
        string $newPrice,
        ?\DateTimeImmutable $changedAt = null,
    ) {
        $this->product = $product;
        $this->oldPrice = $oldPrice;
        $this->newPrice = $newPrice;
        $this->changedAt = $changedAt ?? new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function setProduct(Product $product): void
    {
        $this->product = $product;
    }

    public function getOldPrice(): string
    {
        return $this->oldPrice;
    }

    public function setOldPrice(string $oldPrice): void
    {
        $this->oldPrice = $oldPrice;
    }

    public function getNewPrice(): string
    {
        return $this->newPrice;
    }

    public function setNewPrice(string $newPrice): void
    {
        $this->newPrice = $newPrice;
    }

    public function getChangedAt(): \DateTimeImmutable
    {
        return $this->changedAt;
    }

    public function setChangedAt(\DateTimeImmutable $changedAt): void
    {
        $this->changedAt = $changedAt;
    }

}

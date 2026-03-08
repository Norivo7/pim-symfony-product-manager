<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\Currency;
use App\Enum\ProductStatus;
use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\Table(name: 'products')]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(length: 64)]
    private string $sku;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private string $price;

    #[ORM\Column(length: 3, enumType: Currency::class)]
    private Currency $currency;

    #[ORM\Column(length: 16, enumType: ProductStatus::class)]
    private ProductStatus $status;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $deletedAt = null;

    #[ORM\Column(type: 'integer')]
    #[ORM\Version]
    private int $version = 1;

    /**
     * @var Collection<int, PriceHistoryEntry>
     */
    #[ORM\OneToMany(
        targetEntity: PriceHistoryEntry::class,
        mappedBy: 'product',
        cascade: ['persist']
    )]
    #[ORM\OrderBy(['changedAt' => 'DESC'])]
    private Collection $priceHistoryEntries;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSku(): string
    {
        return $this->sku;
    }

    public function getPrice(): string
    {
        return $this->price;
    }

    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    public function getStatus(): ProductStatus
    {
        return $this->status;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    /**
     * @return Collection<int, PriceHistoryEntry>
     */
    public function getPriceHistoryEntries(): Collection
    {
        return $this->priceHistoryEntries;
    }
    public function __construct(
        string $name,
        string $sku,
        string $price,
        Currency $currency,
        ProductStatus $status,
    ) {
        $now = new \DateTimeImmutable();

        $this->name = $name;
        $this->sku = $sku;
        $this->price = $price;
        $this->currency = $currency;
        $this->status = $status;
        $this->createdAt = $now;
        $this->updatedAt = $now;
        $this->priceHistoryEntries = new ArrayCollection();
    }

    public function updateDetails(
        string $name,
        string $sku,
        Currency $currency,
        ProductStatus $status,
    ): void {
        $this->name = $name;
        $this->sku = $sku;
        $this->currency = $currency;
        $this->status = $status;
        $this->touch();
    }

    public function changePrice(string $newPrice): bool
    {
        if ($this->price === $newPrice) {
            return false;
        }

        $oldPrice = $this->price;

        $this->price = $newPrice;
        $this->touch();

        $priceHistoryEntry = new PriceHistoryEntry($this, $oldPrice, $newPrice);
        $this->priceHistoryEntries->add($priceHistoryEntry);

        return true;
    }

    public function softDelete(): void
    {
        $this->deletedAt = new \DateTimeImmutable();
        $this->touch();
    }

    public function isDeleted(): bool
    {
        return $this->deletedAt !== null;
    }

    private function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

}


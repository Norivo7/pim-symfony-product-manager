<?php

declare(strict_types=1);

namespace App\Request\Product;

use App\Enum\Currency;
use App\Enum\ProductStatus;
use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Attributes as OA;

#[OA\Schema]
final class CreateProductRequest
{
    #[OA\Property(example: "Keyboard HyperY")]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public string $name;

    #[OA\Property(example: "Alloy-001")]
    #[Assert\NotBlank]
    #[Assert\Length(max: 64)]
    public string $sku;

    #[OA\Property(
        description: "Product price with two decimal places",
        example: "199.99"
    )]
    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^\d+(\.\d{1,2})?$/', message: 'Price must be a valid positive decimal number with up to 2 decimal places.')]
    public string $price;

    #[OA\Property(example: "PLN")]
    #[Assert\NotBlank]
    #[Assert\Choice(callback: [Currency::class, 'values'])]
    public string $currency;

    #[OA\Property(example: "active")]
    #[Assert\NotBlank]
    #[Assert\Choice(callback: [ProductStatus::class, 'values'])]
    public string $status;
}

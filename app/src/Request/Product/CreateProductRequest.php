<?php

declare(strict_types=1);

namespace App\Request\Product;

use App\Enum\Currency;
use App\Enum\ProductStatus;
use Symfony\Component\Validator\Constraints as Assert;

final class CreateProductRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public string $name;

    #[Assert\NotBlank]
    #[Assert\Length(max: 64)]
    public string $sku;

    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^\d+(\.\d{1,2})?$/', message: 'Price must be a valid positive decimal number with up to 2 decimal places.')]
    public string $price;

    #[Assert\NotBlank]
    #[Assert\Choice(callback: [Currency::class, 'values'])]
    public string $currency;

    #[Assert\NotBlank]
    #[Assert\Choice(callback: [ProductStatus::class, 'values'])]
    public string $status;
}

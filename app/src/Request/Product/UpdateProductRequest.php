<?php

declare(strict_types=1);

namespace App\Request\Product;

use Symfony\Component\Validator\Constraints as Assert;

final class UpdateProductRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public ?string $name = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 64)]
    public ?string $sku = null;

    #[Assert\NotBlank]
    #[Assert\Regex(
        pattern: '/^\d+(\.\d{1,2})?$/',
        message: 'Price must be a valid positive decimal number with up to 2 decimal places.'
    )]
    public ?string $price = null;

    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['PLN', 'EUR', 'USD'])]
    public ?string $currency = null;

    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['active', 'inactive', 'draft'])]
    public ?string $status = null;

    #[Assert\NotNull]
    #[Assert\Positive]
    public ?int $version = null;
}

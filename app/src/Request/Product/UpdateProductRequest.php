<?php

declare(strict_types=1);

namespace App\Request\Product;

use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Attributes as OA;

#[OA\Schema]
final class UpdateProductRequest
{
    #[OA\Property(example: 'Mouse SteelSeries')]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public ?string $name = null;

    #[OA\Property(example: 'Alloy-002')]
    #[Assert\NotBlank]
    #[Assert\Length(max: 64)]
    public ?string $sku = null;

    #[OA\Property(example: '114.99')]
    #[Assert\NotBlank]
    #[Assert\Regex(
        pattern: '/^\d+(\.\d{1,2})?$/',
        message: 'Price must be a valid positive decimal number with up to 2 decimal places.'
    )]
    public ?string $price = null;

    #[OA\Property(example: 'USD')]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['PLN', 'EUR', 'USD'])]
    public ?string $currency = null;

    #[OA\Property(example: 'inactive')]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['active', 'inactive', 'draft'])]
    public ?string $status = null;

    #[OA\Property(example: 2)]
    #[Assert\NotNull]
    #[Assert\Positive]
    public ?int $version = null;
}

<?php

declare(strict_types=1);

namespace App\Request\Product;

use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Attributes as OA;

#[OA\Schema]
final class ListProductsRequest
{
    #[OA\Property(
        description: 'Filter by product status',
        example: 'active',
        nullable: true
    )]
    #[Assert\Choice(choices: ['active', 'inactive', 'draft'])]
    public ?string $status = null;

    #[OA\Property(
        description: 'Page number',
        default: 1,
        example: 1
    )]
    #[Assert\Positive]
    public int $page = 1;

    #[OA\Property(
        description: 'Items per page',
        default: 10,
        example: 10
    )]
    #[Assert\Positive]
    #[Assert\LessThanOrEqual(100)]
    public int $limit = 10;
}

<?php

declare(strict_types=1);

namespace App\Request\Product;

use Symfony\Component\Validator\Constraints as Assert;

final class ListProductsRequest
{
    #[Assert\Choice(choices: ['active', 'inactive', 'draft'])]
    public ?string $status = null;

    #[Assert\Positive]
    public int $page = 1;

    #[Assert\Positive]
    #[Assert\LessThanOrEqual(100)]
    public int $limit = 10;
}

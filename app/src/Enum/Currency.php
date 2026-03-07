<?php

declare(strict_types=1);

namespace App\Enum;

enum Currency: string
{
    case PLN = 'PLN';
    case EUR = 'EUR';
    case USD = 'USD';
}

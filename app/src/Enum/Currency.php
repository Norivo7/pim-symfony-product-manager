<?php

declare(strict_types=1);

namespace App\Enum;

enum Currency: string
{
    case PLN = 'PLN';
    case EUR = 'EUR';
    case USD = 'USD';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

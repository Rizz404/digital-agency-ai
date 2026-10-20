<?php

namespace App\Modules\Team\Enums;

/**
 * Where in the agency a team area is.
 */
enum Specialty: string
{
    case Brand = 'brand';
    case Growth = 'growth';
    case Digital = 'digital';
    case Strategy = 'strategy';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

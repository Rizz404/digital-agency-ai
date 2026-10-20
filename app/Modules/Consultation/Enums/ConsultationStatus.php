<?php

namespace App\Modules\Consultation\Enums;

/**
 * Where a consultation request stands: the agency confirms or cancels it.
 */
enum ConsultationStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Cancelled = 'cancelled';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

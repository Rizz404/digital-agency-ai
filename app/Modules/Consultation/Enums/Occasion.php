<?php

namespace App\Modules\Consultation\Enums;

/**
 * Occasions a guest can mention so the team can prepare the table.
 */
enum Occasion: string
{
    case BrandLaunch = 'brand_launch';
    case Website = 'website_project';
    case Campaign = 'campaign';
    case Discovery = 'discovery';
    case Automation = 'automation';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

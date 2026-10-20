<?php

namespace App\Modules\Catalog\Enums;

/**
 * The service categories a agency catalog is grouped into.
 */
enum ServiceCategory: string
{
    case Branding = 'branding';
    case WebDesign = 'web_design';
    case PerformanceAds = 'performance_ads';
    case Content = 'content';
    case Seo = 'seo';
    case Automation = 'automation';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

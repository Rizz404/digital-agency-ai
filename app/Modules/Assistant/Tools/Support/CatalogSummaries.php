<?php

namespace App\Modules\Assistant\Tools\Support;

use App\Models\Agency;
use App\Models\Consultant;
use App\Models\ServicePackage;

/**
 * The compact facts about a catalog item or team area that both the model and
 * the guest's cards are given.
 */
final class CatalogSummaries
{
    /**
     * @return array<string, mixed>
     */
    public static function servicePackage(ServicePackage $item, Agency $agency, string $locale): array
    {
        return [
            'service_package_slug' => $item->slug,
            'name' => $item->translatedName($locale),
            'category' => $item->category,
            'price' => (float) $item->price,
            'price_type' => 'estimate',
            'final_quote' => 'Confirmed by the agency team after briefing; no automatic payment.',
            'currency' => $agency->currency,
            'delivery' => $item->delivery,
            'tags' => $item->tags ?? [],
            'requirements' => $item->requirements ?? [],
            'is_sold_out' => $item->is_sold_out,
            'image_url' => $item->image_source,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function consultant(Consultant $area, Agency $agency, string $locale): array
    {
        return [
            'name' => $area->translatedName($locale),
            'specialty' => $area->specialty,
            'min_companies' => $area->min_companies,
            'max_companies' => $area->max_companies,
            'features' => $area->features ?? [],
            'consultation_fee' => (float) $area->consultation_fee,
            'minimum_budget' => $area->minimum_budget !== null ? (float) $area->minimum_budget : null,
            'currency' => $agency->currency,
            'thumbnail_url' => $area->images->first()?->image_source,
        ];
    }

    public static function servicePackageBySlug(Agency $agency, string $slug): ?ServicePackage
    {
        return $agency->servicePackages()->where('is_active', true)->where('slug', $slug)->first();
    }

    public static function consultantBySlug(Agency $agency, string $slug): ?Consultant
    {
        return $agency->consultants()->where('is_active', true)->where('slug', $slug)->first();
    }
}

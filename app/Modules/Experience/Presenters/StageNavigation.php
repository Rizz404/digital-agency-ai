<?php

namespace App\Modules\Experience\Presenters;

use App\Models\Agency;

/**
 * The main catalog of the stage. Sections that live on another page are marked
 * so the stage can fade out, as if the strategist were walking the guest over.
 */
final class StageNavigation
{
    /**
     * @param  array<string, string>  $labels
     * @return list<array{key: string, icon: string, label: string, chip: string, href: string, exit: bool, tour: ?string, topic: string}>
     */
    public static function for(Agency $agency, string $locale, array $labels): array
    {
        $tour = StageCopy::narration($locale);
        $url = fn (string $route) => route($route, ['agencySlug' => $agency->slug, 'lang' => $locale]);

        return [
            ['key' => 'catalog', 'icon' => 'services', 'label' => $labels['catalog_heading'], 'chip' => $labels['chip_catalog'], 'href' => $url('agency.catalog'), 'exit' => true, 'tour' => $tour['tour_catalog'], 'topic' => $labels['nav_catalog_q']],
            ['key' => 'team', 'icon' => 'consultants', 'label' => $labels['team_heading'], 'chip' => $labels['chip_team'], 'href' => $url('agency.team'), 'exit' => true, 'tour' => $tour['tour_team'], 'topic' => $labels['nav_team_q']],
            ['key' => 'facilities', 'icon' => 'sparkle', 'label' => $labels['facilities_heading'], 'chip' => $labels['chip_facilities'], 'href' => $url('agency.facilities'), 'exit' => true, 'tour' => $tour['tour_facilities'], 'topic' => $labels['nav_facilities_q']],
            ['key' => 'info', 'icon' => 'info', 'label' => $labels['info_heading'], 'chip' => $labels['chip_info'], 'href' => $url('agency.info'), 'exit' => true, 'tour' => $tour['tour_info'], 'topic' => $labels['nav_info_q']],
            ['key' => 'consultation', 'icon' => 'calendar', 'label' => $labels['consultation_heading'], 'chip' => $labels['chip_consultation'], 'href' => $url('agency.consultation'), 'exit' => true, 'tour' => $tour['tour_consultation'], 'topic' => $labels['nav_consultation_q']],
            ['key' => 'staff', 'icon' => 'chat', 'label' => $labels['staff_heading'], 'chip' => $labels['chip_staff'], 'href' => $url('agency.staff'), 'exit' => true, 'tour' => $tour['tour_staff'], 'topic' => $labels['nav_staff_q']],
        ];
    }
}

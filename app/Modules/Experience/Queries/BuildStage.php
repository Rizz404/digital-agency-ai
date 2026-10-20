<?php

namespace App\Modules\Experience\Queries;

use App\Models\Agency;
use App\Models\AgencyKnowledgeItem;
use App\Modules\Consultation\Support\ConsultationAvailability;
use App\Modules\Consultation\Support\ConsultationHandover;
use App\Modules\Experience\Presenters\SceneNarrations;
use App\Modules\Experience\Presenters\StageBackdrop;
use App\Modules\Experience\Presenters\StageCopy;
use App\Modules\Experience\Presenters\StageNavigation;

/**
 * Everything the shared stage shell needs, for whichever scene the guest has
 * walked into.
 */
class BuildStage
{
    public function __construct(
        private readonly ConsultationHandover $handover,
        private readonly ConsultationAvailability $availability,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function handle(Agency $agency, string $locale, string $scene): array
    {
        $servicePackages = $agency->servicePackages()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->values();

        $consultants = $agency->consultants()
            ->where('is_active', true)
            ->with(['images' => fn ($q) => $q->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get()
            ->values();

        $facilities = $agency->knowledgeItems()->where('is_active', true)
            ->whereIn('category', [AgencyKnowledgeItem::CATEGORY_FACILITIES, AgencyKnowledgeItem::CATEGORY_EVENTS, AgencyKnowledgeItem::CATEGORY_LOCATION])
            ->orderBy('sort_order')->get()->values();

        $labels = StageCopy::labels($locale);

        return [
            'agency' => $agency,
            'servicePackages' => $servicePackages,
            'consultants' => $consultants,
            'locale' => $locale,
            'scene' => $scene,
            'backdrop' => StageBackdrop::for($scene),
            'navItems' => StageNavigation::for($agency, $locale, $labels),
            'supportedLocales' => config('agency.locales'),
            'labels' => $labels,
            'stageCopy' => StageCopy::copy($locale),
            'terms' => StageCopy::terms($locale),
            'wizard' => StageCopy::wizard($locale),
            'narration' => StageCopy::narration($locale),
            'catalogNarrations' => SceneNarrations::catalog($agency, $servicePackages, $locale),
            'teamNarrations' => SceneNarrations::team($agency, $consultants, $locale),
            'staffLinks' => $this->handover->forStaff($agency, $locale),
            'today' => now($agency->timezone)->toDateString(),
            'maxAdvanceDays' => ConsultationAvailability::MAX_ADVANCE_DAYS,
            'slots' => collect($this->availability->slotsFor($agency))
                ->map(fn (string $slot) => ['value' => $slot, 'label' => ConsultationAvailability::slotRange($slot)])
                ->all(),
            'infoItems' => $agency->knowledgeItems()->where('is_active', true)
                ->whereIn('category', [AgencyKnowledgeItem::CATEGORY_GENERAL, AgencyKnowledgeItem::CATEGORY_POLICIES, AgencyKnowledgeItem::CATEGORY_FAQ])
                ->orderBy('sort_order')->get()->groupBy('category'),
            'facilities' => $facilities,
            'sceneNarrations' => SceneNarrations::scenes($agency, $facilities, $locale),
        ];
    }
}

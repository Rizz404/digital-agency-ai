<?php

namespace App\Http\Controllers;

use App\Http\Middleware\ResolveAgency;
use App\Models\Agency;
use App\Models\AgencyKnowledgeItem;
use App\Models\Consultant;
use App\Models\ServicePackage;
use App\Modules\Experience\Presenters\StageBackdrop;
use App\Modules\Experience\Presenters\StageCopy;
use App\Modules\Experience\Queries\BuildStage;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * The guest-facing stage: one page per scene, all built from the same shell
 * data. The agency and the language are resolved by the ResolveAgency middleware.
 */
class AgencyPageController extends Controller
{
    public function __construct(private readonly BuildStage $stage) {}

    public function index(Request $request): View
    {
        $agencies = Agency::published()->orderBy('name')->get();

        $locale = in_array($request->query('lang'), config('agency.locales'), true)
            ? $request->query('lang')
            : ($agencies->first()?->default_locale ?? config('agency.default_locale'));

        return view('opening', [
            'agencies' => $agencies,
            'locale' => $locale,
            'supportedLocales' => config('agency.locales'),
            'opening' => StageCopy::opening($locale),
            'avatarImage' => StageBackdrop::avatar(),
            'backdrop' => StageBackdrop::for('home'),
        ]);
    }

    public function show(Request $request): View
    {
        return view('agency.show', $this->stageData($request, 'home'));
    }

    public function catalog(Request $request): View
    {
        return view('agency.catalog-index', $this->stageData($request, 'catalog'));
    }

    public function servicePackage(Request $request, string $agencySlug, string $itemSlug): View
    {
        $data = $this->stageData($request, 'service-package');
        $items = $data['servicePackages'];
        $index = $items->search(fn (ServicePackage $item) => $item->slug === $itemSlug);

        abort_if($index === false, 404);

        return view('agency.catalog-detail', [
            ...$data,
            'servicePackage' => $items[$index],
            'itemIndex' => $index,
            ...$this->neighbours($items, $index, 'Item'),
        ]);
    }

    public function team(Request $request): View
    {
        return view('agency.team-index', $this->stageData($request, 'team'));
    }

    public function consultant(Request $request, string $agencySlug, string $areaSlug): View
    {
        $data = $this->stageData($request, 'consultant');
        $areas = $data['consultants'];
        $index = $areas->search(fn (Consultant $area) => $area->slug === $areaSlug);

        abort_if($index === false, 404);

        return view('agency.team-detail', [
            ...$data,
            'consultant' => $areas[$index],
            'areaIndex' => $index,
            ...$this->neighbours($areas, $index, 'Area'),
        ]);
    }

    public function facilities(Request $request): View
    {
        return view('agency.facilities-index', $this->stageData($request, 'facilities'));
    }

    public function facility(Request $request, string $agencySlug, int $facilityId): View
    {
        $data = $this->stageData($request, 'facility');
        $facilities = $data['facilities'];
        $index = $facilities->search(fn (AgencyKnowledgeItem $item) => $item->id === $facilityId);

        abort_if($index === false, 404);

        return view('agency.facility-detail', [
            ...$data,
            'facility' => $facilities[$index],
            'facilityIndex' => $index,
            ...$this->neighbours($facilities, $index, 'Facility'),
        ]);
    }

    public function info(Request $request): View
    {
        return view('agency.info-index', $this->stageData($request, 'info'));
    }

    public function staff(Request $request): View
    {
        return view('agency.staff-index', $this->stageData($request, 'staff'));
    }

    public function consultationScene(Request $request): View
    {
        return view('agency.consultation-index', [
            ...$this->stageData($request, 'consultation'),
            'preselectedArea' => (string) $request->query('area', ''),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function stageData(Request $request, string $scene): array
    {
        return $this->stage->handle(ResolveAgency::agency($request), ResolveAgency::locale($request), $scene);
    }

    /**
     * The previous and next entry around an index, wrapping at both ends.
     *
     * @param  Collection<int, mixed>  $items
     * @return array<string, mixed>
     */
    private function neighbours(Collection $items, int $index, string $suffix): array
    {
        return [
            'previous'.$suffix => $items[($index - 1 + $items->count()) % $items->count()],
            'next'.$suffix => $items[($index + 1) % $items->count()],
        ];
    }
}

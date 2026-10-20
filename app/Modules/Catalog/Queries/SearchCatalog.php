<?php

namespace App\Modules\Catalog\Queries;

use App\Models\Agency;
use App\Models\ServicePackage;
use App\Support\SearchText;
use Illuminate\Support\Collection;

/**
 * The active catalog, narrowed by keyword, category, service_tag need or budget.
 */
class SearchCatalog
{
    /**
     * @return Collection<int, ServicePackage>
     */
    public function handle(Agency $agency, string $query = '', ?string $category = null, ?string $service_tag = null, ?float $maxPrice = null, int $limit = 6): Collection
    {
        $query = SearchText::normalize($query);

        return $agency->servicePackages()
            ->where('is_active', true)
            ->when($category, fn ($q) => $q->where('category', $category))
            ->orderBy('sort_order')
            ->get()
            ->filter(fn (ServicePackage $item) => ! $service_tag || $item->hasTag($service_tag))
            ->filter(fn (ServicePackage $item) => $maxPrice === null || (float) $item->price <= $maxPrice)
            ->filter(fn (ServicePackage $item) => SearchText::matches(implode(' ', [
                $item->name,
                $item->description,
                $item->category,
                implode(' ', $item->tags ?? []),
                collect($item->translations ?? [])->flatten()->implode(' '),
            ]), $query))
            ->take($limit);
    }
}

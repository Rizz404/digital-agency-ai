<?php

namespace App\Modules\Knowledge\Queries;

use App\Models\Agency;
use App\Models\AgencyKnowledgeItem;
use App\Support\SearchText;
use Illuminate\Support\Collection;

/**
 * The agency's approved knowledge base, searched by keyword. The only source the
 * AI Digital Strategist may state agency facts from.
 */
class SearchKnowledge
{
    /**
     * @return Collection<int, AgencyKnowledgeItem>
     */
    public function handle(Agency $agency, string $query, ?string $category = null, int $limit = 5): Collection
    {
        $query = SearchText::normalize($query);

        return $agency->knowledgeItems()
            ->where('is_active', true)
            ->when($category, fn ($q) => $q->where('category', $category))
            ->get()
            ->filter(fn (AgencyKnowledgeItem $item) => SearchText::matches(
                $item->title.' '.$item->body.' '.implode(' ', $item->tags ?? []).' '.collect($item->translations ?? [])->flatten()->implode(' '),
                $query,
            ))
            ->take($limit);
    }
}

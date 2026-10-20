<?php

namespace App\Modules\Operations\Queries;

use App\Models\Agency;
use App\Models\Consultation;
use App\Models\HandoverRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * What the agency team sees first when they sign in: counts of what they manage
 * and the few things waiting for them.
 */
class DashboardMetrics
{
    /**
     * @return array{service_packages: int, consultants: int, knowledge_items: int, pending_consultations: int, open_handovers: int}
     */
    public function counts(Agency $agency): array
    {
        return [
            'service_packages' => $agency->servicePackages()->count(),
            'consultants' => $agency->consultants()->count(),
            'knowledge_items' => $agency->knowledgeItems()->count(),
            'pending_consultations' => $agency->consultations()->where('status', Consultation::STATUS_PENDING)->count(),
            'open_handovers' => $this->openHandovers($agency)->count(),
        ];
    }

    /**
     * @return Collection<int, Consultation>
     */
    public function recentConsultations(Agency $agency, int $limit = 5): Collection
    {
        return $agency->consultations()->latest()->take($limit)->with('consultant')->get();
    }

    /**
     * @return Collection<int, HandoverRequest>
     */
    public function latestOpenHandovers(Agency $agency, int $limit = 5): Collection
    {
        return $this->openHandovers($agency)->latest()->take($limit)->with('conversation')->get();
    }

    /**
     * @return Builder<HandoverRequest>
     */
    private function openHandovers(Agency $agency): Builder
    {
        return HandoverRequest::whereHas('conversation', fn ($q) => $q->where('agency_id', $agency->id))
            ->where('status', HandoverRequest::STATUS_OPEN);
    }
}

<?php

namespace App\Modules\Conversation\Actions;

use App\Models\Agency;
use App\Models\Conversation;

/**
 * Remembers where in the UI the guest is, so the strategist can answer for "this
 * drink" or the consultation they are filling in. Only values that exist for
 * this agency are kept; nothing personal is stored here.
 */
class RememberUiContext
{
    /**
     * @param  array{scene?: ?string, selected_service_package?: ?string, selected_consultant?: ?string, selected_facility?: ?int, consultation?: ?array<string, mixed>}  $context
     */
    public function handle(Agency $agency, Conversation $conversation, array $context): void
    {
        $updates = [];

        if (in_array($context['scene'] ?? null, Conversation::SCENES, true)) {
            $updates['current_scene'] = $context['scene'];
        }

        if (filled($context['selected_service_package'] ?? null)) {
            $servicePackage = $agency->servicePackages()->where('is_active', true)->where('slug', $context['selected_service_package'])->first();

            if ($servicePackage) {
                $updates['selected_service_package_id'] = $servicePackage->id;
            }
        }

        if (filled($context['selected_consultant'] ?? null)) {
            $area = $agency->consultants()->where('is_active', true)->where('slug', $context['selected_consultant'])->first();

            if ($area) {
                $updates['selected_consultant_id'] = $area->id;
            }
        }

        if (filled($context['selected_facility'] ?? null)) {
            $facility = $agency->knowledgeItems()->where('is_active', true)->whereKey($context['selected_facility'])->first();

            if ($facility) {
                $updates['selected_facility_id'] = $facility->id;
            }
        }

        if (array_key_exists('consultation', $context)) {
            $updates['consultation_state'] = $context['consultation'] ?: null;
        }

        if ($updates !== []) {
            $conversation->update($updates);
        }
    }
}

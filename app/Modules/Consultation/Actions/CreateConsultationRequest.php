<?php

namespace App\Modules\Consultation\Actions;

use App\Models\Agency;
use App\Models\Consultant;
use App\Models\Consultation;
use App\Models\ConsultationSlot;
use App\Models\Conversation;
use App\Modules\Consultation\Support\ConsultationAvailability;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/**
 * Creates a pending consultation request and holds one consultation slot for it. Both the
 * consultation wizard and the AI Digital Strategist's consultation tool end up here.
 */
class CreateConsultationRequest
{
    public function __construct(private readonly ConsultationAvailability $availability) {}

    /**
     * Returns null when no table is free in that slot any more.
     *
     * @param  array{consultation_date: CarbonImmutable, time_slot: string, companies: int, occasion?: ?string, requester_name: string, requester_email?: ?string, requester_phone?: ?string, contact_type?: ?string, notes?: ?string}  $data
     */
    public function handle(Agency $agency, Consultant $area, array $data, ?Conversation $conversation = null): ?Consultation
    {
        if ($area->agency_id !== $agency->id || ! $area->is_active || ! $this->availability->fitsParty($area, (int) $data['companies'])) {
            return null;
        }

        return DB::transaction(function () use ($agency, $area, $data, $conversation) {
            $quote = $this->availability->quote($area, $data['consultation_date'], $data['time_slot'], lockForUpdate: true);

            if (! $quote) {
                return null;
            }

            $consultation = Consultation::create([
                'reference' => Consultation::generateReference(),
                'agency_id' => $agency->id,
                'consultant_id' => $area->id,
                'conversation_id' => $conversation?->id,
                'consultant_name' => $area->name,
                'duration_minutes' => ConsultationAvailability::SLOT_MINUTES,
                'requester_name' => $data['requester_name'],
                'requester_email' => $data['requester_email'] ?? null,
                'requester_phone' => $data['requester_phone'] ?? null,
                'contact_type' => $data['contact_type'] ?? null,
                'consultation_date' => $data['consultation_date']->toDateString(),
                'time_slot' => $data['time_slot'],
                'companies' => $data['companies'],
                'occasion' => $data['occasion'] ?? null,
                'consultation_total' => $quote['fee'],
                'status' => Consultation::STATUS_PENDING,
                'notes' => $data['notes'] ?? null,
            ]);

            ConsultationSlot::where('consultant_id', $consultation->consultant_id)
                ->whereDate('consultation_date', $consultation->consultation_date->toDateString())
                ->where('time_slot', $consultation->time_slot)
                ->increment('reserved_slots');

            return $consultation;
        });
    }
}

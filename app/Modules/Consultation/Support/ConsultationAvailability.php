<?php

namespace App\Modules\Consultation\Support;

use App\Models\Agency;
use App\Models\Consultant;
use App\Models\ConsultationSlot;
use Carbon\CarbonImmutable;

/**
 * The single place where consultation availability and slot quotes are computed.
 * Both the AI Digital Strategist tools and the guest-facing consultation wizard go
 * through here, so they can never disagree.
 */
class ConsultationAvailability
{
    /**
     * How far ahead a table can be requested.
     */
    public const MAX_ADVANCE_DAYS = 60;

    /**
     * Every slot is a fixed 60-minute sitting.
     */
    public const SLOT_MINUTES = 60;

    /**
     * Whether the party size suits the team area.
     */
    public function fitsParty(Consultant $area, int $companies): bool
    {
        return $companies === 1;
    }

    /**
     * The slot start times the agency takes consultations for, earliest first.
     *
     * @return list<string>
     */
    public function slotsFor(Agency $agency): array
    {
        return ConsultationSlot::query()
            ->whereIn('consultant_id', $agency->consultants()->where('is_active', true)->select('id'))
            ->distinct()
            ->orderBy('time_slot')
            ->pluck('time_slot')
            ->all();
    }

    /**
     * "11:00–12:00" for a slot that starts at 11:00.
     */
    public static function slotRange(string $slot): string
    {
        $start = CarbonImmutable::createFromFormat('H:i', $slot);

        return $start->format('H:i').'–'.$start->addMinutes(self::SLOT_MINUTES)->format('H:i');
    }

    /**
     * Whether the slot has already begun in the agency's own time zone.
     */
    public function isSlotInPast(Agency $agency, CarbonImmutable $date, string $slot): bool
    {
        $start = CarbonImmutable::parse($date->toDateString().' '.$slot, $agency->timezone);

        return $start->lessThanOrEqualTo(CarbonImmutable::now($agency->timezone));
    }

    /**
     * Checks one slot. Returns null if there is no free consultation slot in it, which is
     * the one place availability truth comes from, never the model.
     *
     * @return array{date: string, time_slot: string, time_range: string, available_slots: int, fee: float, minimum_budget: ?float}|null
     */
    public function quote(Consultant $area, CarbonImmutable $date, string $slot, bool $lockForUpdate = false): ?array
    {
        $query = ConsultationSlot::where('consultant_id', $area->id)
            ->whereDate('consultation_date', $date->toDateString())
            ->where('time_slot', $slot);

        if ($lockForUpdate) {
            $query->lockForUpdate();
        }

        $inventory = $query->first();

        if (! $inventory || ! $inventory->isAvailable()) {
            return null;
        }

        return [
            'date' => $date->toDateString(),
            'time_slot' => $slot,
            'time_range' => self::slotRange($slot),
            'available_slots' => $inventory->availableSlots(),
            'fee' => (float) $area->consultation_fee,
            'minimum_budget' => $area->minimum_budget !== null ? (float) $area->minimum_budget : null,
        ];
    }

    /**
     * Every slot of the day that still has a free consultation slot and has not started.
     *
     * @return list<string>
     */
    public function availableSlots(Consultant $area, CarbonImmutable $date): array
    {
        $agency = $area->agency;

        return ConsultationSlot::where('consultant_id', $area->id)
            ->whereDate('consultation_date', $date->toDateString())
            ->orderBy('time_slot')
            ->get()
            ->filter(fn (ConsultationSlot $row) => $row->isAvailable() && ! $this->isSlotInPast($agency, $date, $row->time_slot))
            ->pluck('time_slot')
            ->values()
            ->all();
    }
}

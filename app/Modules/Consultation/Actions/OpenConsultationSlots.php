<?php

namespace App\Modules\Consultation\Actions;

use App\Models\Agency;
use App\Models\ConsultationSlot;
use App\Modules\Consultation\Support\ConsultationAvailability;
use Carbon\CarbonImmutable;

final class OpenConsultationSlots
{
    public function handle(Agency $agency): int
    {
        $today = CarbonImmutable::now($agency->timezone)->startOfDay();
        $count = 0;
        foreach ($agency->consultants()->where('is_active', true)->orderBy('id')->get() as $consultant) {
            $rows = [];
            for ($day = 0; $day <= ConsultationAvailability::MAX_ADVANCE_DAYS; $day++) {
                $date = $today->addDays($day);
                if ($date->isWeekend()) {
                    continue;
                }
                foreach (config('agency.consultation_slots') as $slot) {
                    $start = $date->setTimeFromTimeString($slot);
                    $open = $date->setTimeFromTimeString($agency->opening_time);
                    $close = $date->setTimeFromTimeString($agency->closing_time);
                    if ($start->lessThan($open) || $start->addMinutes(ConsultationAvailability::SLOT_MINUTES)->greaterThan($close)) {
                        continue;
                    }
                    $rows[] = [
                        'consultant_id' => $consultant->id,
                        'consultation_date' => $date->toDateString(),
                        'time_slot' => $slot,
                        'total_slots' => 1,
                        'reserved_slots' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            foreach (array_chunk($rows, 100) as $chunk) {
                ConsultationSlot::upsert($chunk, ['consultant_id', 'consultation_date', 'time_slot'], ['total_slots']);
            }
            $count += count($rows);
        }

        return $count;
    }
}

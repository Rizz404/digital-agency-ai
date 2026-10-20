<?php

namespace App\Modules\Consultation\Actions;

use App\Models\Consultation;
use App\Models\ConsultationSlot;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ChangeConsultationStatus
{
    public function handle(Consultation $consultation, string $status): Consultation
    {
        return DB::transaction(function () use ($consultation, $status) {
            $current = Consultation::whereKey($consultation->id)->lockForUpdate()->firstOrFail();
            if (! in_array($status, [Consultation::STATUS_CONFIRMED, Consultation::STATUS_CANCELLED], true)) {
                throw ValidationException::withMessages(['status' => 'Unsupported consultation status.']);
            }
            if ($current->status === $status) {
                return $current;
            }
            if ($current->status === Consultation::STATUS_CANCELLED) {
                throw ValidationException::withMessages(['status' => 'A cancelled consultation cannot be reopened. Submit a new request.']);
            }
            if ($status === Consultation::STATUS_CANCELLED) {
                ConsultationSlot::where('consultant_id', $current->consultant_id)
                    ->whereDate('consultation_date', $current->consultation_date->toDateString())
                    ->where('time_slot', $current->time_slot)
                    ->where('reserved_slots', '>', 0)
                    ->decrement('reserved_slots');
            }
            $current->update(['status' => $status]);

            return $current;
        });
    }
}

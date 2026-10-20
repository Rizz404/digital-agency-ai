<?php

namespace App\Modules\Consultation\Actions;

use App\Models\Agency;
use App\Models\Consultant;
use App\Modules\Consultation\Support\ConsultationAvailability;
use App\Modules\Consultation\Validation\ConsultationRules;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

/**
 * Turns the guest's already well-formed request into the team area and
 * date it names, or tells them in their own language what does not fit.
 */
class ResolveConsultationTarget
{
    public function __construct(private readonly ConsultationAvailability $availability) {}

    /**
     * @param  array<string, mixed>  $data
     * @return array{0: Consultant, 1: CarbonImmutable}
     *
     * @throws ValidationException
     */
    public function handle(Agency $agency, array $data, string $locale): array
    {
        $area = $agency->consultants()->where('is_active', true)->where('slug', $data['consultant_slug'])->first();

        if (! $area) {
            throw ValidationException::withMessages(['consultant_slug' => ConsultationRules::message('area_unknown', $locale)]);
        }

        $date = CarbonImmutable::parse($data['consultation_date'], $agency->timezone)->startOfDay();
        $today = CarbonImmutable::now($agency->timezone)->startOfDay();

        if ($today->diffInDays($date) > ConsultationAvailability::MAX_ADVANCE_DAYS) {
            throw ValidationException::withMessages([
                'consultation_date' => ConsultationRules::message('date_far', $locale, ['max' => (string) ConsultationAvailability::MAX_ADVANCE_DAYS]),
            ]);
        }

        if (! in_array($data['time_slot'], $this->availability->slotsFor($agency), true)) {
            throw ValidationException::withMessages(['time_slot' => ConsultationRules::message('slot_unknown', $locale)]);
        }

        if ($this->availability->isSlotInPast($agency, $date, $data['time_slot'])) {
            throw ValidationException::withMessages(['time_slot' => ConsultationRules::message('slot_past', $locale)]);
        }

        if (! $this->availability->fitsParty($area, (int) $data['companies'])) {
            throw ValidationException::withMessages(['companies' => ConsultationRules::message('capacity', $locale)]);
        }

        return [$area, $date];
    }
}

<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\Consultant;
use App\Models\Consultation;
use App\Modules\Consultation\Enums\ConsultationStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Consultation>
 */
class ConsultationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reference' => Consultation::generateReference(),
            'agency_id' => Agency::factory(),
            'consultant_id' => fn (array $attributes) => Consultant::factory()->create(['agency_id' => $attributes['agency_id']])->id,
            'consultant_name' => 'Demo Consultant',
            'duration_minutes' => 60,
            'requester_name' => fake()->name(),
            'requester_phone' => '+62 811 1111 222',
            'contact_type' => 'phone',
            'consultation_date' => now()->addDays(3)->toDateString(),
            'time_slot' => '11:00',
            'companies' => 1,
            'consultation_total' => 0,
            'status' => ConsultationStatus::Pending->value,
        ];
    }

    public function confirmed(): static
    {
        return $this->state(fn () => ['status' => ConsultationStatus::Confirmed->value]);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => ['status' => ConsultationStatus::Cancelled->value]);
    }
}

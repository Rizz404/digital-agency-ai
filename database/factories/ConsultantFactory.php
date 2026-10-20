<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\Consultant;
use App\Modules\Team\Enums\Specialty;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Consultant>
 */
class ConsultantFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'agency_id' => Agency::factory(),
            'name' => Str::title($name),
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'specialty' => Specialty::Brand->value,
            'min_companies' => 1,
            'max_companies' => 1,
            'features' => [],
            'consultation_fee' => 0,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function forCompanies(int $min, int $max): static
    {
        return $this->state(fn () => ['min_companies' => $min, 'max_companies' => $max]);
    }

    public function withFee(float $fee): static
    {
        return $this->state(fn () => ['consultation_fee' => $fee]);
    }
}

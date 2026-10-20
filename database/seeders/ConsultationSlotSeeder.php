<?php

namespace Database\Seeders;

use App\Models\Agency;
use App\Modules\Consultation\Actions\OpenConsultationSlots;
use Illuminate\Database\Seeder;

class ConsultationSlotSeeder extends Seeder
{
    public function run(): void
    {
        app(OpenConsultationSlots::class)->handle(Agency::where('slug', AgencySeeder::SLUG)->firstOrFail());
    }
}

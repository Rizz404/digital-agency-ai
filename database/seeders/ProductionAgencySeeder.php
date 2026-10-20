<?php

namespace Database\Seeders;

use App\Models\Agency;
use Illuminate\Database\Seeder;

class ProductionAgencySeeder extends Seeder
{
    public function run(): void
    {
        Agency::firstOrCreate(['slug' => 'digital-agency-ai'], [
            'name' => 'Digital Agency AI', 'timezone' => 'Asia/Jakarta', 'currency' => 'IDR',
            'default_locale' => 'id', 'opening_time' => '09:00', 'closing_time' => '17:00', 'public_status' => 'draft',
        ]);
    }
}

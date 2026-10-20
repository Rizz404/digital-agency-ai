<?php

namespace Database\Seeders;

use App\Models\Agency;
use Illuminate\Database\Seeder;

class ConsultantImageSeeder extends Seeder
{
    public function run(): void
    {
        $agency = Agency::where('slug', AgencySeeder::SLUG)->firstOrFail();
        foreach ($agency->consultants as $consultant) {
            $consultant->images()->updateOrCreate(['sort_order' => 0], [
                'image_url' => 'images/agency/'.$consultant->slug.'.svg',
                'alt_text' => $consultant->name.' · illustrated demo profile',
            ]);
        }
    }
}

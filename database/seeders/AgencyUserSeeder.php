<?php

namespace Database\Seeders;

use App\Models\Agency;
use App\Models\User;
use Illuminate\Database\Seeder;

class AgencyUserSeeder extends Seeder
{
    public function run(): void
    {
        $agency = Agency::where('name', AgencySeeder::NAME)->firstOrFail();
        $owner = User::where('email', UserSeeder::OWNER_EMAIL)->firstOrFail();

        $agency->users()->syncWithoutDetaching([
            $owner->id => ['role' => 'owner', 'status' => 'active'],
        ]);
    }
}

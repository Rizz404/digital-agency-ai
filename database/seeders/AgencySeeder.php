<?php

namespace Database\Seeders;

use App\Models\Agency;
use Illuminate\Database\Seeder;

class AgencySeeder extends Seeder
{
    public const NAME = 'Digital Agency AI';

    public const SLUG = 'digital-agency-ai';

    public function run(): void
    {
        Agency::updateOrCreate(['slug' => self::SLUG], [
            'name' => 'Digital Agency AI',
            'slug' => 'digital-agency-ai',
            'description' => 'Studio demo untuk strategi merek, desain digital, dan pertumbuhan bisnis. Seluruh harga dan profil merupakan contoh.',
            'translations' => [
                'en' => [
                    'description' => 'A demo studio for brand strategy, digital design and business growth. All prices and profiles are illustrative.',
                ],
                'ja' => [
                    'description' => 'ブランド戦略、デジタルデザイン、ビジネス成長のデモスタジオ。価格とプロフィールはサンプルです。',
                ],
            ],
            'address' => null,
            'city' => 'Jakarta',
            'country' => 'Indonesia',
            'phone' => null,
            'whatsapp' => null,
            'email' => null,
            'instagram' => null,
            'timezone' => 'Asia/Jakarta',
            'currency' => 'IDR',
            'default_locale' => 'id',
            'opening_time' => '09:00',
            'closing_time' => '17:00',
            'public_status' => 'published',
        ]);
    }
}

<?php

namespace Database\Seeders;

use App\Models\Agency;
use Illuminate\Database\Seeder;

class ConsultantSeeder extends Seeder
{
    public function run(): void
    {
        $agency = Agency::where('slug', AgencySeeder::SLUG)->firstOrFail();
        foreach ([
            [
                'slug' => 'maya-brand-strategy',
                'name' => 'Maya · Brand Strategy',
                'specialty' => 'brand',
                'min_companies' => 1,
                'max_companies' => 1,
                'description' => 'Membantu merumuskan posisi merek dan arah identitas visual. Profil demo.',
                'translations' => [
                    'en' => [
                        'name' => 'Maya · Brand Strategy',
                        'description' => 'Helps shape brand positioning and visual identity direction. Demo profile.',
                    ],
                    'ja' => [
                        'name' => 'Maya · Brand Strategy',
                        'description' => 'ブランドの位置づけとビジュアル方針を整理します。デモ用プロフィール。',
                    ],
                ],
                'features' => [
                    'brand_audit',
                    'online',
                ],
                'consultation_fee' => 0,
                'minimum_budget' => null,
                'is_active' => true,
                'sort_order' => 0,
            ],
            [
                'slug' => 'raka-digital-growth',
                'name' => 'Raka · Digital Growth',
                'specialty' => 'growth',
                'min_companies' => 1,
                'max_companies' => 1,
                'description' => 'Membahas prioritas akuisisi, pengukuran, dan eksperimen kampanye. Profil demo.',
                'translations' => [
                    'en' => [
                        'name' => 'Raka · Digital Growth',
                        'description' => 'Discusses acquisition priorities, measurement and campaign experiments. Demo profile.',
                    ],
                    'ja' => [
                        'name' => 'Raka · Digital Growth',
                        'description' => '顧客獲得の優先順位、測定、キャンペーンの検証を相談できます。デモ用プロフィール。',
                    ],
                ],
                'features' => [
                    'campaign_planning',
                    'conversion',
                    'online',
                ],
                'consultation_fee' => 0,
                'minimum_budget' => null,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'slug' => 'nara-experience-design',
                'name' => 'Nara · Experience Design',
                'specialty' => 'digital',
                'min_companies' => 1,
                'max_companies' => 1,
                'description' => 'Memetakan kebutuhan pengguna dan pengalaman situs yang lebih jelas. Profil demo.',
                'translations' => [
                    'en' => [
                        'name' => 'Nara · Experience Design',
                        'description' => 'Maps user needs and clearer website experiences. Demo profile.',
                    ],
                    'ja' => [
                        'name' => 'Nara · Experience Design',
                        'description' => 'ユーザーの課題と分かりやすいサイト体験を整理します。デモ用プロフィール。',
                    ],
                ],
                'features' => [
                    'ux_review',
                    'online',
                ],
                'consultation_fee' => 0,
                'minimum_budget' => null,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'slug' => 'dimas-strategy-systems',
                'name' => 'Dimas · Strategy & Systems',
                'specialty' => 'strategy',
                'min_companies' => 1,
                'max_companies' => 1,
                'description' => 'Menyusun prioritas digital dan peluang otomasi berdasarkan brief. Profil demo.',
                'translations' => [
                    'en' => [
                        'name' => 'Dimas · Strategy & Systems',
                        'description' => 'Shapes digital priorities and automation opportunities from your brief. Demo profile.',
                    ],
                    'ja' => [
                        'name' => 'Dimas · Strategy & Systems',
                        'description' => '課題に応じたデジタル施策の優先順位と自動化を考えます。デモ用プロフィール。',
                    ],
                ],
                'features' => [
                    'workflow',
                    'online',
                ],
                'consultation_fee' => 0,
                'minimum_budget' => null,
                'is_active' => true,
                'sort_order' => 3,
            ],
        ] as $consultant) {
            $agency->consultants()->updateOrCreate(['slug' => $consultant['slug']], $consultant);
        }
    }
}

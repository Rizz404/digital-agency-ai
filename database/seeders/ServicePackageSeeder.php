<?php

namespace Database\Seeders;

use App\Models\Agency;
use Illuminate\Database\Seeder;

class ServicePackageSeeder extends Seeder
{
    public function run(): void
    {
        $agency = Agency::where('slug', AgencySeeder::SLUG)->firstOrFail();
        foreach ([
            [
                'slug' => 'brand-foundations',
                'name' => 'Brand Foundations',
                'category' => 'branding',
                'price' => 8500000,
                'estimated_days' => 14,
                'delivery' => 'project',
                'tags' => [
                    'startup',
                    'signature',
                ],
                'requirements' => [
                    'brand_brief',
                ],
                'description' => 'Identitas merek yang konsisten: discovery, arah visual, logo, dan panduan dasar. Contoh cakupan; final melalui penawaran tim.',
                'translations' => [
                    'en' => [
                        'name' => 'Brand Foundations',
                        'description' => 'A cohesive brand identity: discovery, visual direction, logo and a foundational guide. Sample scope; final proposal by our team.',
                    ],
                    'ja' => [
                        'name' => 'Brand Foundations',
                        'description' => '一貫したブランドのためのヒアリング、ビジュアル方針、ロゴ、基本ガイド。対応範囲は最終提案で確認します。',
                    ],
                ],
                'image_url' => 'images/agency/brand-foundations.svg',
                'is_active' => true,
                'is_featured' => true,
                'is_sold_out' => false,
                'sort_order' => 0,
            ],
            [
                'slug' => 'digital-flagship',
                'name' => 'Digital Flagship',
                'category' => 'web_design',
                'price' => 15000000,
                'estimated_days' => 25,
                'delivery' => 'project',
                'tags' => [
                    'ecommerce',
                    'signature',
                ],
                'requirements' => [
                    'brand_assets',
                    'content_materials',
                ],
                'description' => 'Situs pemasaran dengan strategi konten, desain responsif, dan fondasi analitik. Hosting dan integrasi khusus dibahas terpisah.',
                'translations' => [
                    'en' => [
                        'name' => 'Digital Flagship',
                        'description' => 'A marketing website with content strategy, responsive design and analytics foundations. Hosting and custom integrations are scoped separately.',
                    ],
                    'ja' => [
                        'name' => 'Digital Flagship',
                        'description' => 'コンテンツ戦略、レスポンシブデザイン、分析の基盤を備えたサイト。ホスティングや個別連携は別途相談します。',
                    ],
                ],
                'image_url' => 'images/agency/digital-flagship.svg',
                'is_active' => true,
                'is_featured' => true,
                'is_sold_out' => false,
                'sort_order' => 1,
            ],
            [
                'slug' => 'growth-campaign',
                'name' => 'Growth Campaign',
                'category' => 'performance_ads',
                'price' => 6500000,
                'estimated_days' => 20,
                'delivery' => 'monthly',
                'tags' => [
                    'growth',
                    'bestseller',
                ],
                'requirements' => [
                    'analytics_access',
                    'brand_assets',
                ],
                'description' => 'Perencanaan kampanye, eksperimen materi iklan, dan laporan optimasi. Estimasi biaya pengelolaan, tidak termasuk belanja iklan.',
                'translations' => [
                    'en' => [
                        'name' => 'Growth Campaign',
                        'description' => 'Campaign planning, creative experiments and optimization reporting. Estimated management fee; ad spend is excluded.',
                    ],
                    'ja' => [
                        'name' => 'Growth Campaign',
                        'description' => 'キャンペーン企画、広告クリエイティブの検証、改善レポート。広告出稿費は含まれません。',
                    ],
                ],
                'image_url' => 'images/agency/growth-campaign.svg',
                'is_active' => true,
                'is_featured' => true,
                'is_sold_out' => false,
                'sort_order' => 2,
            ],
            [
                'slug' => 'content-engine',
                'name' => 'Content Engine',
                'category' => 'content',
                'price' => 4500000,
                'estimated_days' => 15,
                'delivery' => 'monthly',
                'tags' => [
                    'startup',
                    'local_business',
                ],
                'requirements' => [
                    'brand_brief',
                    'content_materials',
                ],
                'description' => 'Pilar konten, kalender editorial, serta produksi aset digital sesuai brief. Volume dan revisi disepakati dalam penawaran.',
                'translations' => [
                    'en' => [
                        'name' => 'Content Engine',
                        'description' => 'Content pillars, editorial planning and digital asset production based on your brief. Volume and revisions are agreed in the proposal.',
                    ],
                    'ja' => [
                        'name' => 'Content Engine',
                        'description' => 'コンテンツの柱、編集計画、デジタル素材制作。制作数と修正回数は提案書で合意します。',
                    ],
                ],
                'image_url' => 'images/agency/content-engine.svg',
                'is_active' => true,
                'is_featured' => false,
                'is_sold_out' => false,
                'sort_order' => 3,
            ],
            [
                'slug' => 'search-discovery',
                'name' => 'Search & Discovery',
                'category' => 'seo',
                'price' => 5000000,
                'estimated_days' => 20,
                'delivery' => 'monthly',
                'tags' => [
                    'growth',
                    'local_business',
                ],
                'requirements' => [
                    'website_access',
                    'analytics_access',
                ],
                'description' => 'Audit SEO, pemetaan kata kunci, dan prioritas perbaikan konten. Tidak menjanjikan peringkat atau pertumbuhan tertentu.',
                'translations' => [
                    'en' => [
                        'name' => 'Search & Discovery',
                        'description' => 'SEO audit, keyword mapping and content improvement priorities. No guaranteed ranking or growth outcomes.',
                    ],
                    'ja' => [
                        'name' => 'Search & Discovery',
                        'description' => 'SEO診断、キーワード設計、コンテンツ改善の優先順位。順位や成長率を保証するものではありません。',
                    ],
                ],
                'image_url' => 'images/agency/search-discovery.svg',
                'is_active' => true,
                'is_featured' => false,
                'is_sold_out' => false,
                'sort_order' => 4,
            ],
            [
                'slug' => 'workflow-studio',
                'name' => 'Workflow Studio',
                'category' => 'automation',
                'price' => 12000000,
                'estimated_days' => 21,
                'delivery' => 'sprint',
                'tags' => [
                    'enterprise',
                    'growth',
                ],
                'requirements' => [
                    'brand_brief',
                    'analytics_access',
                ],
                'description' => 'Pemetaan alur pemasaran dan prototipe otomasi sesuai kebutuhan. Biaya layanan pihak ketiga dihitung terpisah.',
                'translations' => [
                    'en' => [
                        'name' => 'Workflow Studio',
                        'description' => 'Marketing workflow mapping and an automation prototype tailored to your needs. Third-party service fees are separate.',
                    ],
                    'ja' => [
                        'name' => 'Workflow Studio',
                        'description' => 'マーケティング業務の整理と自動化の試作。外部サービスの費用は別途必要です。',
                    ],
                ],
                'image_url' => 'images/agency/workflow-studio.svg',
                'is_active' => true,
                'is_featured' => false,
                'is_sold_out' => false,
                'sort_order' => 5,
            ],
        ] as $package) {
            $agency->servicePackages()->updateOrCreate(['slug' => $package['slug']], $package);
        }
    }
}

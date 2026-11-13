<?php

namespace Database\Seeders;

use App\Models\Agency;
use Illuminate\Database\Seeder;

class AgencyKnowledgeItemSeeder extends Seeder
{
    public function run(): void
    {
        $agency = Agency::where('slug', AgencySeeder::SLUG)->firstOrFail();
        foreach ([
            [
                'category' => 'facilities',
                'image_url' => 'images/agency/brand-foundations.webp',
                'title' => 'Discovery & brief',
                'body' => 'Kami mulai dari tujuan bisnis, audiens, kebutuhan, kisaran anggaran, dan tenggat. Brief awal membantu tim menyusun cakupan yang sesuai.',
                'translations' => [
                    'en' => [
                        'title' => 'Discovery & brief',
                        'body' => 'We start with business goals, audience, requirements, budget range and timeline. An initial brief helps define a suitable scope.',
                    ],
                    'ja' => [
                        'title' => 'ヒアリング',
                        'body' => '目標、対象顧客、要件、予算、納期からヒアリングします。初期情報をもとに対応範囲を整理します。',
                    ],
                ],
                'tags' => [
                    'agensi',
                    'agency',
                    'studio',
                    'proyek',
                    'project',
                    'Discovery & brief',
                ],
                'is_active' => true,
                'sort_order' => 0,
            ],
            [
                'category' => 'facilities',
                'image_url' => 'images/agency/digital-flagship.webp',
                'title' => 'Strategi & konsep',
                'body' => 'Tim merangkum prioritas dan menyusun arah kreatif. Deliverable, revisi, serta jadwal final disepakati sebelum pekerjaan dimulai.',
                'translations' => [
                    'en' => [
                        'title' => 'Strategy & concept',
                        'body' => 'Our team defines priorities and a creative direction. Deliverables, revisions and the final timeline are agreed before work starts.',
                    ],
                    'ja' => [
                        'title' => '戦略・コンセプト',
                        'body' => '優先順位とクリエイティブ方針を整理します。成果物、修正回数、日程は着手前に合意します。',
                    ],
                ],
                'tags' => [
                    'agensi',
                    'agency',
                    'studio',
                    'proyek',
                    'project',
                    'Strategi & konsep',
                ],
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'category' => 'facilities',
                'image_url' => 'images/agency/content-engine.webp',
                'title' => 'Produksi & kolaborasi',
                'body' => 'Pengerjaan mengikuti cakupan yang disepakati dengan titik umpan balik yang jelas. Perubahan di luar cakupan memerlukan persetujuan penawaran tambahan.',
                'translations' => [
                    'en' => [
                        'title' => 'Production & collaboration',
                        'body' => 'Work follows the agreed scope with clear feedback checkpoints. Out-of-scope changes require an additional approved proposal.',
                    ],
                    'ja' => [
                        'title' => '制作・協業',
                        'body' => '合意した範囲で制作し、フィードバックの機会を設けます。範囲外の変更は追加提案への合意が必要です。',
                    ],
                ],
                'tags' => [
                    'agensi',
                    'agency',
                    'studio',
                    'proyek',
                    'project',
                    'Produksi & kolaborasi',
                ],
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'category' => 'facilities',
                'image_url' => 'images/agency/workflow-studio.webp',
                'title' => 'Handover & evaluasi',
                'body' => 'Tim menyerahkan hasil sesuai kontrak dan membahas langkah selanjutnya. Hak penggunaan aset dan dukungan mengikuti penawaran final.',
                'translations' => [
                    'en' => [
                        'title' => 'Handover & review',
                        'body' => 'The team hands over contracted deliverables and discusses next steps. Asset rights and support follow the final agreement.',
                    ],
                    'ja' => [
                        'title' => '納品・振り返り',
                        'body' => '契約した成果物を納品し、次の施策を検討します。利用権とサポートは最終契約に従います。',
                    ],
                ],
                'tags' => [
                    'agensi',
                    'agency',
                    'studio',
                    'proyek',
                    'project',
                    'Handover & evaluasi',
                ],
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'category' => 'general',
                'title' => 'Tentang studio demo',
                'body' => 'Digital Agency AI adalah demo studio agensi. Profil konsultan, harga, dan cakupan adalah contoh, bukan rekam jejak atau penawaran bisnis yang disahkan.',
                'translations' => [
                    'en' => [
                        'title' => 'About this demo studio',
                        'body' => 'Digital Agency AI is a demo agency studio. Consultant profiles, prices and scope are examples, not verified credentials or approved business offers.',
                    ],
                    'ja' => [
                        'title' => 'デモスタジオについて',
                        'body' => 'Digital Agency AIはデモです。担当者、価格、範囲はサンプルであり、確認済みの実績や正式な提案ではありません。',
                    ],
                ],
                'tags' => [
                    'agensi',
                    'agency',
                    'studio',
                    'proyek',
                    'project',
                    'Tentang studio demo',
                ],
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'category' => 'policies',
                'title' => 'Estimasi & pembayaran',
                'body' => 'Harga paket adalah estimasi awal. Penawaran final, jadwal, deliverable, dan ketentuan pembayaran dikonfirmasi tim setelah brief. Situs tidak menarik pembayaran otomatis.',
                'translations' => [
                    'en' => [
                        'title' => 'Estimates & payment',
                        'body' => 'Package prices are starting estimates. Final quotes, schedules, deliverables and payment terms are confirmed after briefing. The site takes no automatic payments.',
                    ],
                    'ja' => [
                        'title' => '概算・支払い',
                        'body' => '価格は概算です。最終見積もり、日程、成果物、支払い条件はヒアリング後に確認します。サイト上で自動決済は行いません。',
                    ],
                ],
                'tags' => [
                    'agensi',
                    'agency',
                    'studio',
                    'proyek',
                    'project',
                    'Estimasi & pembayaran',
                ],
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'category' => 'policies',
                'title' => 'Konsultasi & pembatalan',
                'body' => 'Satu konsultasi berdurasi 60 menit untuk satu perusahaan/pemesan per slot konsultan. Permintaan berstatus pending sampai dikonfirmasi tim. Hubungi tim dengan nomor referensi untuk perubahan atau pembatalan. Pembatalan melepaskan slot.',
                'translations' => [
                    'en' => [
                        'title' => 'Consultation & cancellation',
                        'body' => 'Each consultation lasts 60 minutes for one company/requester per consultant slot. Requests remain pending until team confirmation. Contact the team with your reference for changes or cancellation. Cancellation releases the slot.',
                    ],
                    'ja' => [
                        'title' => '相談・キャンセル',
                        'body' => '相談は60分で1枠1社・1申込者です。担当者の確認までは依頼中です。変更・キャンセルは受付番号を添えてご連絡ください。キャンセルで枠が空きます。',
                    ],
                ],
                'tags' => [
                    'agensi',
                    'agency',
                    'studio',
                    'proyek',
                    'project',
                    'Konsultasi & pembatalan',
                ],
                'is_active' => true,
                'sort_order' => 6,
            ],
            [
                'category' => 'faq',
                'title' => 'Apakah hasil kampanye dijamin?',
                'body' => 'Tidak. Kami tidak menjanjikan peringkat, konversi, atau pertumbuhan tertentu. Target, metode pengukuran, dan evaluasi dibahas bersama tim.',
                'translations' => [
                    'en' => [
                        'title' => 'Are campaign results guaranteed?',
                        'body' => 'No. We do not guarantee rankings, conversions or specific growth. Goals, measurement and evaluation are discussed with the team.',
                    ],
                    'ja' => [
                        'title' => '成果は保証されますか？',
                        'body' => '順位、成約数、特定の成長を保証しません。目標、測定方法、評価は担当者と相談します。',
                    ],
                ],
                'tags' => [
                    'agensi',
                    'agency',
                    'studio',
                    'proyek',
                    'project',
                    'Apakah hasil kampanye dijamin?',
                ],
                'is_active' => true,
                'sort_order' => 7,
            ],
            [
                'category' => 'faq',
                'title' => 'Bagaimana memulai?',
                'body' => 'Jelajahi katalog, ceritakan tujuan bisnis kepada AI Digital Strategist, lalu ajukan konsultasi atau minta dihubungkan ke tim. Jangan kirim password, API key, atau data rahasia melalui chat.',
                'translations' => [
                    'en' => [
                        'title' => 'How do I get started?',
                        'body' => 'Explore our catalog, share your business goals with AI Digital Strategist, then request a consultation or team handover. Do not share passwords, API keys or secrets in chat.',
                    ],
                    'ja' => [
                        'title' => '始めるには？',
                        'body' => 'カタログを見て、AI Digital Strategistに目標を伝え、相談または担当者への引き継ぎをご依頼ください。パスワードやAPIキーなどは送信しないでください。',
                    ],
                ],
                'tags' => [
                    'agensi',
                    'agency',
                    'studio',
                    'proyek',
                    'project',
                    'Bagaimana memulai?',
                ],
                'is_active' => true,
                'sort_order' => 8,
            ],
        ] as $item) {
            $agency->knowledgeItems()->updateOrCreate(['category' => $item['category'], 'sort_order' => $item['sort_order']], $item);
        }
    }
}

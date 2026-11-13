<?php

namespace Tests\Feature;

use App\Models\Agency;
use App\Models\Consultant;
use App\Models\ConsultationSlot;
use App\Models\ServicePackage;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgencyStageTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;

    private ServicePackage $branding;

    private Consultant $terrace;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->agency = Agency::create([
            'name' => 'Demo Agency', 'slug' => 'demo', 'public_status' => 'published', 'city' => 'Jakarta', 'country' => 'Indonesia', 'currency' => 'IDR',
            'default_locale' => 'id', 'opening_time' => '08:00', 'closing_time' => '22:00', 'phone' => '+62 21 1234', 'whatsapp' => '6281234567890',
            'description' => 'Agensi hangat di Kemang.',
        ]);

        $this->branding = $this->agency->servicePackages()->create([
            'name' => 'Brand Sprint', 'slug' => 'brand-sprint', 'category' => 'branding', 'price' => 32000, 'description' => 'Strategi identitas merek.',
            'translations' => ['en' => ['name' => 'Brand Sprint', 'description' => 'Brand identity strategy.']], 'tags' => ['growth', 'signature'], 'requirements' => ['brand_brief'], 'delivery' => 'sprint',
            'image_url' => 'images/agency/brand-foundations.svg', 'is_active' => true, 'sort_order' => 0,
        ]);
        $this->agency->servicePackages()->create(['name' => 'Automation Sprint', 'slug' => 'automation-sprint', 'category' => 'automation', 'price' => 40000, 'is_sold_out' => true, 'is_active' => true, 'sort_order' => 1]);
        $this->agency->servicePackages()->create(['name' => 'Retired Package', 'slug' => 'retired', 'category' => 'branding', 'price' => 1000, 'is_active' => false]);

        $this->terrace = $this->agency->consultants()->create([
            'name' => 'Growth Consultant', 'slug' => 'growth-consultant', 'specialty' => 'growth', 'min_companies' => 1, 'max_companies' => 1, 'features' => ['online'],
            'consultation_fee' => 0, 'is_active' => true,
        ]);
        $this->terrace->images()->create(['image_url' => 'images/team/growth-consultant.svg', 'alt_text' => 'Terrace', 'sort_order' => 0]);
        ConsultationSlot::create([
            'consultant_id' => $this->terrace->id, 'consultation_date' => CarbonImmutable::now('Asia/Jakarta')->addDays(3)->toDateString(), 'time_slot' => '13:00',
            'total_slots' => 2, 'reserved_slots' => 0,
        ]);

        $this->agency->knowledgeItems()->create(['category' => 'facilities', 'title' => 'Wi-Fi gratis', 'body' => 'Wi-Fi cepat untuk semua tamu.', 'tags' => ['wifi'], 'is_active' => true]);
        $this->agency->knowledgeItems()->create(['category' => 'policies', 'title' => 'Kebijakan reservasi', 'body' => 'Hubungi tim untuk pembatalan.', 'is_active' => true]);
        $this->agency->knowledgeItems()->create(['category' => 'faq', 'title' => 'Ada musala?', 'body' => 'Ada, di sebelah toilet.', 'is_active' => true]);
    }

    public function test_the_opening_screen_lists_published_agencies(): void
    {
        Agency::create(['name' => 'Draft Agency', 'slug' => 'draft']);

        $this->get('/')
            ->assertOk()
            ->assertSee('Demo Agency')
            ->assertDontSee('Draft Agency')
            ->assertSee(route('agency.catalog', ['agencySlug' => 'demo', 'lang' => 'id']), false);
    }

    public function test_the_counter_greets_companies_in_the_requested_language(): void
    {
        $this->get('/demo')->assertOk()->assertSee('Demo Agency')->assertSee('Ide besar,');
        $this->get('/demo?lang=en')->assertOk()->assertSee('Big ideas,')->assertSee('lang="en"', false);
        $this->get('/demo?lang=ja')->assertOk()->assertSee('アイデアを、');
        $this->get('/demo?lang=xx')->assertOk()->assertSee('Ide besar,');
    }

    public function test_every_scene_renders_and_carries_the_strategist_chat(): void
    {
        $routes = [
            'agency.show', 'agency.catalog', 'agency.team', 'agency.facilities', 'agency.info', 'agency.staff', 'agency.consultation',
        ];

        foreach ($routes as $route) {
            $this->get(route($route, ['agencySlug' => 'demo']))
                ->assertOk()
                ->assertSee('id="strategist-app"', false)
                ->assertSee('data-scene=', false);
        }
    }

    public function test_unpublished_and_unknown_agencies_are_not_found(): void
    {
        Agency::create(['name' => 'Draft', 'slug' => 'draft']);

        $this->get('/draft')->assertNotFound();
        $this->get('/draft/catalog')->assertNotFound();
        $this->get('/nowhere')->assertNotFound();
    }

    public function test_the_catalog_scene_narrates_from_stored_data_and_lists_active_items(): void
    {
        $this->get('/demo/catalog?lang=en')
            ->assertOk()
            ->assertSee('Explore 2 creative packages, with estimates from IDR 32.000')
            ->assertSee('Brand Sprint')
            ->assertSee('Automation Sprint')
            ->assertDontSee('Retired Package');
    }

    public function test_a_service_package_shows_its_price_requirements_and_tags(): void
    {
        $this->get('/demo/catalog/brand-sprint?lang=en')
            ->assertOk()
            ->assertSee('Brand Sprint')
            ->assertSee('IDR 32.000')
            ->assertSee('Brand identity strategy.')
            ->assertSee('Brand brief')
            ->assertSee('Studio pick')
            ->assertSee('Please prepare: Brand brief.')
            ->assertSee('images/agency/brand-foundations.webp', false);

        $this->get('/demo/catalog/automation-sprint?lang=en')->assertOk()->assertSee('Not taking new projects');

        $this->get('/demo/catalog/retired')->assertNotFound();
        $this->get('/demo/catalog/nothing-here')->assertNotFound();
    }

    public function test_the_team_scenes_show_capacity_and_fees(): void
    {
        $this->get('/demo/team?lang=en')->assertOk()->assertSee('Growth Consultant')->assertSee('Meet Growth Consultant.');

        $this->get('/demo/team/growth-consultant?lang=en')
            ->assertOk()
            ->assertSee('Growth Consultant')
            ->assertSee('Online discussion')
            ->assertSee('Initial consultation is free; scheduling awaits team confirmation.')
            ->assertSee('area=growth-consultant', false);

        $this->get('/demo/team/nowhere')->assertNotFound();
    }

    public function test_the_facility_scenes_show_stored_services(): void
    {
        $facility = $this->agency->knowledgeItems()->where('category', 'facilities')->firstOrFail();

        $this->get('/demo/facilities')->assertOk()->assertSee('Wi-Fi gratis')->assertDontSee('Kebijakan reservasi');
        $this->get('/demo/facilities/'.$facility->id)->assertOk()->assertSee('Wi-Fi cepat untuk semua tamu.');
        $this->get('/demo/facilities/99999')->assertNotFound();
    }

    public function test_the_info_scene_shows_opening_hours_policies_and_faq(): void
    {
        $this->get('/demo/info?lang=en')
            ->assertOk()
            ->assertSee('08:00')
            ->assertSee('22:00')
            ->assertSee('We are open from 08:00 to 22:00.')
            ->assertSee('Kebijakan reservasi')
            ->assertSee('Ada musala?')
            ->assertSee('Agensi hangat di Kemang.');
    }

    public function test_the_staff_scene_offers_whatsapp_and_phone(): void
    {
        $this->get('/demo/staff')
            ->assertOk()
            ->assertSee('https://wa.me/6281234567890', false)
            ->assertSee('tel:+62211234', false);
    }

    public function test_the_consultation_scene_offers_slots_and_consultants(): void
    {
        $this->get('/demo/consultation?area=growth-consultant&lang=en')
            ->assertOk()
            ->assertSee('data-wizard', false)
            ->assertSee('data-preselect-area="growth-consultant"', false)
            ->assertSee('13:00–14:00')
            ->assertSee('Growth Consultant');
    }
}

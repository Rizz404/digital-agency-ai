<?php

namespace Tests\Feature;

use App\Models\Agency;
use App\Models\Consultation;
use App\Models\ConsultationSlot;
use App\Models\Conversation;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Support\RunsStrategistTools;
use Tests\TestCase;

class StrategistConsultationToolTest extends TestCase
{
    use RefreshDatabase, RunsStrategistTools;

    public function test_ai_consultation_tool_creates_a_reference_and_holds_one_table(): void
    {
        $agency = Agency::create(['name' => 'Demo', 'slug' => 'demo', 'public_status' => 'published', 'currency' => 'IDR']);
        $area = $agency->consultants()->create(['name' => 'Strategy Consultant', 'slug' => 'strategy-consultant', 'specialty' => 'strategy', 'min_companies' => 1, 'max_companies' => 1, 'consultation_fee' => 150000, 'is_active' => true]);
        $conversation = Conversation::create(['agency_id' => $agency->id, 'guest_token' => (string) Str::uuid(), 'locale' => 'en']);

        $date = CarbonImmutable::now($agency->timezone)->addDays(5);
        foreach (['11:00', '13:00'] as $slot) {
            ConsultationSlot::create(['consultant_id' => $area->id, 'consultation_date' => $date->toDateString(), 'time_slot' => $slot, 'total_slots' => 1, 'reserved_slots' => 0]);
        }

        $input = [
            'consultant_slug' => 'strategy-consultant', 'date' => $date->toDateString(), 'time_slot' => '11:00',
            'companies' => 1, 'requester_name' => 'Ayu', 'requester_phone' => '+62 811 111 222',
        ];

        $quote = $this->runTool($agency, $conversation, 'check_consultation_availability', $input);
        $this->assertTrue($quote['ui']['available']);
        $this->assertSame(150000.0, $quote['ui']['quote']['consultation_fee']);

        $result = $this->runTool($agency, $conversation, 'create_consultation_request', $input);

        $consultation = Consultation::firstOrFail();
        $this->assertSame($consultation->reference, $result['ui']['consultation']['consultation_reference']);
        $this->assertSame(1, $consultation->companies);
        $this->assertSame('11:00', $consultation->time_slot);
        $this->assertSame(150000.0, (float) $consultation->consultation_total);
        $this->assertSame([1, 0], ConsultationSlot::orderBy('time_slot')->pluck('reserved_slots')->all());

        $repeated = $this->runTool($agency, $conversation, 'create_consultation_request', $input);
        $this->assertSame($consultation->reference, $repeated['ui']['consultation']['consultation_reference']);
        $this->assertSame(1, Consultation::count());

        $taken = $this->runTool($agency, $conversation, 'create_consultation_request', [...$input, 'requester_name' => 'Budi']);
        $this->assertNull($taken['ui']);
        $this->assertSame(1, Consultation::count());

        $full = $this->runTool($agency, $conversation, 'check_consultation_availability', $input);
        $this->assertFalse($full['ui']['available']);
        $this->assertStringContainsString('13:00', $full['text']);
    }

    public function test_the_tools_refuse_a_party_that_does_not_fit_and_a_slot_that_does_not_exist(): void
    {
        $agency = Agency::create(['name' => 'Demo', 'slug' => 'demo', 'public_status' => 'published']);
        $area = $agency->consultants()->create(['name' => 'Bar', 'slug' => 'digital', 'specialty' => 'digital', 'min_companies' => 1, 'max_companies' => 2, 'is_active' => true]);
        $conversation = Conversation::create(['agency_id' => $agency->id, 'guest_token' => (string) Str::uuid(), 'locale' => 'en']);
        $date = CarbonImmutable::now($agency->timezone)->addDays(2);
        ConsultationSlot::create(['consultant_id' => $area->id, 'consultation_date' => $date->toDateString(), 'time_slot' => '15:00', 'total_slots' => 2, 'reserved_slots' => 0]);

        $base = ['consultant_slug' => 'digital', 'date' => $date->toDateString(), 'requester_name' => 'Ayu', 'requester_phone' => '+62 811 111 222'];

        $tooMany = $this->runTool($agency, $conversation, 'create_consultation_request', [...$base, 'time_slot' => '15:00', 'companies' => 5]);
        $this->assertNull($tooMany['ui']);

        $unknownSlot = $this->runTool($agency, $conversation, 'check_consultation_availability', [...$base, 'time_slot' => '03:00', 'companies' => 1]);
        $this->assertStringContainsString('15:00', $unknownSlot['text']);

        $past = $this->runTool($agency, $conversation, 'check_consultation_availability', [...$base, 'date' => '2001-01-01', 'time_slot' => '15:00', 'companies' => 1]);
        $this->assertStringContainsString('past', $past['text']);

        $this->assertSame(0, Consultation::count());
    }

    public function test_the_consultation_tool_needs_a_usable_name_and_contact_before_it_books(): void
    {
        $agency = Agency::create(['name' => 'Demo', 'slug' => 'demo', 'public_status' => 'published']);
        $area = $agency->consultants()->create(['name' => 'Bar', 'slug' => 'digital', 'specialty' => 'digital', 'min_companies' => 1, 'max_companies' => 1, 'is_active' => true]);
        $conversation = Conversation::create(['agency_id' => $agency->id, 'guest_token' => (string) Str::uuid(), 'locale' => 'en']);
        $date = CarbonImmutable::now($agency->timezone)->addDays(2);
        ConsultationSlot::create(['consultant_id' => $area->id, 'consultation_date' => $date->toDateString(), 'time_slot' => '15:00', 'total_slots' => 2, 'reserved_slots' => 0]);

        $base = ['consultant_slug' => 'digital', 'date' => $date->toDateString(), 'time_slot' => '15:00', 'companies' => 1];

        foreach ([
            ['requester_phone' => '+62 811 111 222'],
            ['requester_name' => 'Ayu'],
            ['requester_name' => 'Ayu', 'requester_phone' => 'call me'],
            ['requester_name' => 'Ayu', 'requester_email' => 'not-an-email'],
        ] as $guest) {
            $this->assertNull($this->runTool($agency, $conversation, 'create_consultation_request', [...$base, ...$guest])['ui']);
        }

        $this->assertSame(0, Consultation::count());

        $byEmail = $this->runTool($agency, $conversation, 'create_consultation_request', [...$base, 'requester_name' => 'Ayu', 'requester_email' => 'ayu@example.com']);

        $this->assertNotNull($byEmail['ui']);
        $this->assertSame('email', Consultation::firstOrFail()->contact_type);
    }

    public function test_catalog_search_filters_by_diet_and_budget_and_hides_unpublished_items(): void
    {
        $agency = Agency::create(['name' => 'Demo', 'slug' => 'demo', 'public_status' => 'published', 'currency' => 'IDR']);
        $agency->servicePackages()->create(['name' => 'Aglio Olio', 'slug' => 'aglio-olio', 'category' => 'content', 'price' => 48000, 'tags' => ['startup'], 'is_active' => true]);
        $agency->servicePackages()->create(['name' => 'Club Sandwich', 'slug' => 'club-sandwich', 'category' => 'content', 'price' => 52000, 'tags' => ['local_business'], 'is_active' => true]);
        $agency->servicePackages()->create(['name' => 'Secret Dish', 'slug' => 'secret', 'category' => 'content', 'price' => 10000, 'tags' => ['startup'], 'is_active' => false]);
        $conversation = Conversation::create(['agency_id' => $agency->id, 'guest_token' => (string) Str::uuid(), 'locale' => 'en']);

        $vegan = $this->runTool($agency, $conversation, 'search_catalog', ['service_tag' => 'startup']);
        $this->assertSame(['aglio-olio'], collect($vegan['ui']['items'])->pluck('service_package_slug')->all());

        $cheap = $this->runTool($agency, $conversation, 'search_catalog', ['category' => 'content', 'max_price' => 50000]);
        $this->assertSame(['aglio-olio'], collect($cheap['ui']['items'])->pluck('service_package_slug')->all());

        $none = $this->runTool($agency, $conversation, 'search_catalog', ['query' => 'sushi']);
        $this->assertNull($none['ui']);
    }
}

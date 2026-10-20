<?php

namespace Tests\Feature;

use App\Models\Agency;
use App\Models\Consultant;
use App\Models\Consultation;
use App\Models\ConsultationSlot;
use App\Models\Conversation;
use App\Modules\Consultation\Actions\ChangeConsultationStatus;
use App\Modules\Consultation\Support\ConsultationAvailability;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ConsultationRequestTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;

    private Consultant $lounge;

    private Consultant $terrace;

    private string $date;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::create(['name' => 'Demo', 'slug' => 'demo', 'public_status' => 'published', 'currency' => 'IDR', 'default_locale' => 'en', 'whatsapp' => '6281234567890', 'email' => 'hello@demo.test']);
        $this->lounge = $this->agency->consultants()->create(['name' => 'Brand Consultant', 'slug' => 'brand-consultant', 'specialty' => 'brand', 'min_companies' => 1, 'max_companies' => 1, 'consultation_fee' => 0, 'is_active' => true]);
        $this->terrace = $this->agency->consultants()->create(['name' => 'Growth Consultant', 'slug' => 'growth-consultant', 'specialty' => 'growth', 'min_companies' => 1, 'max_companies' => 1, 'consultation_fee' => 25000, 'is_active' => true]);

        $this->date = CarbonImmutable::now($this->agency->timezone)->addDays(5)->toDateString();

        foreach ([$this->lounge, $this->terrace] as $area) {
            foreach (['11:00', '13:00'] as $slot) {
                ConsultationSlot::create(['consultant_id' => $area->id, 'consultation_date' => $this->date, 'time_slot' => $slot, 'total_slots' => 1, 'reserved_slots' => 0]);
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return [
            'consultant_slug' => 'brand-consultant', 'consultation_date' => $this->date, 'time_slot' => '11:00', 'companies' => 1,
            'requester_name' => 'Ayu', 'contact_type' => 'whatsapp', 'contact_value' => '+62 811 1234 5678', 'locale' => 'en',
            ...$overrides,
        ];
    }

    public function test_a_quote_reports_the_fee_and_free_tables(): void
    {
        $this->postJson('/demo/consultation/quote', $this->payload(['consultant_slug' => 'growth-consultant']))
            ->assertOk()
            ->assertJsonPath('available', true)
            ->assertJsonPath('fee', 25000)
            ->assertJsonPath('time_range', '11:00–12:00')
            ->assertJsonPath('available_slots', 1);
    }

    public function test_submitting_creates_a_pending_consultation_and_holds_a_table(): void
    {
        $this->postJson('/demo/consultation', $this->payload(['occasion' => 'brand_launch', 'special_request' => 'Please review our brief']))
            ->assertCreated()
            ->assertJsonPath('status', Consultation::STATUS_PENDING)
            ->assertJsonStructure(['reference', 'handover' => ['whatsapp_url', 'phone_url', 'email_url']]);

        $consultation = Consultation::firstOrFail();
        $this->assertStringStartsWith('DA-', $consultation->reference);
        $this->assertSame('brand_launch', $consultation->occasion);
        $this->assertSame('Please review our brief', $consultation->notes);
        $this->assertSame(1, ConsultationSlot::where('consultant_id', $this->lounge->id)->where('time_slot', '11:00')->value('reserved_slots'));
    }

    public function test_a_full_slot_is_refused_with_alternatives(): void
    {
        ConsultationSlot::where('consultant_id', $this->lounge->id)->where('time_slot', '11:00')->update(['reserved_slots' => 1]);

        $response = $this->postJson('/demo/consultation', $this->payload())
            ->assertUnprocessable()
            ->assertJsonValidationErrors('consultant_slug');

        $alternatives = collect($response->json('alternatives'));
        $this->assertSame('13:00', $alternatives->firstWhere('type', 'slot')['time_slot']);
        $this->assertSame('growth-consultant', $alternatives->firstWhere('type', 'area')['slug']);
        $this->assertSame(0, Consultation::count());
    }

    public function test_the_party_must_suit_the_consultant(): void
    {
        $this->postJson('/demo/consultation', $this->payload(['companies' => 5]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('companies');
    }

    public function test_dates_and_slots_are_validated(): void
    {
        $this->postJson('/demo/consultation', $this->payload(['consultation_date' => '2001-01-01']))->assertUnprocessable()->assertJsonValidationErrors('consultation_date');

        $far = CarbonImmutable::now($this->agency->timezone)->addDays(ConsultationAvailability::MAX_ADVANCE_DAYS + 5)->toDateString();
        $this->postJson('/demo/consultation', $this->payload(['consultation_date' => $far]))->assertUnprocessable()->assertJsonValidationErrors('consultation_date');

        $this->postJson('/demo/consultation', $this->payload(['time_slot' => '03:00']))->assertUnprocessable()->assertJsonValidationErrors('time_slot');
        $this->postJson('/demo/consultation', $this->payload(['consultant_slug' => 'nowhere']))->assertUnprocessable()->assertJsonValidationErrors('consultant_slug');
    }

    public function test_contact_details_are_checked_for_the_chosen_method(): void
    {
        $this->postJson('/demo/consultation', $this->payload(['contact_type' => 'email', 'contact_value' => 'not-an-email']))->assertUnprocessable()->assertJsonValidationErrors('contact_value');
        $this->postJson('/demo/consultation', $this->payload(['contact_type' => 'phone', 'contact_value' => 'abc']))->assertUnprocessable()->assertJsonValidationErrors('contact_value');
        $this->postJson('/demo/consultation', $this->payload(['contact_type' => 'email', 'contact_value' => 'ayu@example.com']))->assertCreated();

        $this->assertSame('ayu@example.com', Consultation::firstOrFail()->requester_email);
    }

    public function test_a_consultation_links_to_the_conversation_it_came_from(): void
    {
        $conversation = Conversation::create(['agency_id' => $this->agency->id, 'guest_token' => (string) Str::uuid(), 'locale' => 'en']);

        $this->postJson('/demo/consultation', $this->payload(['guest_token' => $conversation->guest_token]))->assertCreated();

        $this->assertSame($conversation->id, Consultation::firstOrFail()->conversation_id);
    }

    public function test_cancelling_gives_the_table_back(): void
    {
        $this->postJson('/demo/consultation', $this->payload())->assertCreated();
        $consultation = Consultation::firstOrFail();

        app(ChangeConsultationStatus::class)->handle($consultation, Consultation::STATUS_CANCELLED);

        $this->assertSame(0, ConsultationSlot::where('consultant_id', $this->lounge->id)->where('time_slot', '11:00')->value('reserved_slots'));
    }

    public function test_unpublished_agencies_take_no_consultations(): void
    {
        $this->agency->update(['public_status' => 'draft']);

        $this->postJson('/demo/consultation/quote', $this->payload())->assertNotFound();
        $this->postJson('/demo/consultation', $this->payload())->assertNotFound();
    }
}

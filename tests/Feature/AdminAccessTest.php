<?php

namespace Tests\Feature;

use App\Models\Agency;
use App\Models\Consultation;
use App\Models\ConsultationSlot;
use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Models\HandoverRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;

    private Agency $otherAgency;

    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->agency = Agency::create(['name' => 'Demo', 'slug' => 'demo', 'public_status' => 'published']);
        $this->otherAgency = Agency::create(['name' => 'Other', 'slug' => 'other', 'public_status' => 'published']);

        $this->staff = User::factory()->create();
        $this->agency->users()->attach($this->staff->id, ['role' => 'owner', 'status' => 'active']);
    }

    private function foreignConsultation(): Consultation
    {
        $area = $this->otherAgency->consultants()->create(['name' => 'Foreign', 'slug' => 'foreign', 'specialty' => 'brand', 'is_active' => true]);

        return Consultation::create([
            'reference' => 'DA-FOREIGN', 'agency_id' => $this->otherAgency->id, 'consultant_id' => $area->id, 'consultant_name' => 'Snapshot Consultant', 'requester_name' => 'Foreign Guest',
            'consultation_date' => now()->addDays(3)->toDateString(), 'time_slot' => '11:00', 'companies' => 1,
        ]);
    }

    private function foreignHandover(): HandoverRequest
    {
        $conversation = Conversation::create(['agency_id' => $this->otherAgency->id, 'guest_token' => (string) Str::uuid(), 'locale' => 'en']);

        return HandoverRequest::create(['conversation_id' => $conversation->id, 'reason' => 'complaint', 'summary' => 'Foreign complaint']);
    }

    public function test_visitors_are_sent_to_the_login_page_for_every_admin_screen(): void
    {
        foreach (['admin.dashboard', 'admin.service-packages.index', 'admin.consultants.index', 'admin.knowledge-items.index', 'admin.consultations.index', 'admin.handovers.index'] as $route) {
            $this->get(route($route))->assertRedirect(route('admin.login'));
        }
    }

    public function test_staff_only_see_consultations_of_their_own_agency(): void
    {
        $area = $this->agency->consultants()->create(['name' => 'Lounge', 'slug' => 'lounge', 'specialty' => 'brand', 'is_active' => true]);
        Consultation::create([
            'reference' => 'DA-MINE', 'agency_id' => $this->agency->id, 'consultant_id' => $area->id, 'consultant_name' => 'Snapshot Consultant', 'requester_name' => 'My Guest',
            'consultation_date' => now()->addDays(3)->toDateString(), 'time_slot' => '13:00', 'companies' => 1,
        ]);
        $this->foreignConsultation();

        $this->actingAs($this->staff)->get(route('admin.consultations.index'))
            ->assertOk()
            ->assertSee('DA-MINE')
            ->assertDontSee('DA-FOREIGN');
    }

    public function test_staff_cannot_change_another_agencies_consultation(): void
    {
        $consultation = $this->foreignConsultation();

        $this->actingAs($this->staff)
            ->patch(route('admin.consultations.status', $consultation), ['status' => 'confirmed'])
            ->assertNotFound();

        $this->assertSame(Consultation::STATUS_PENDING, $consultation->fresh()->status);
    }

    public function test_cancelling_a_consultation_releases_its_table(): void
    {
        $area = $this->agency->consultants()->create(['name' => 'Lounge', 'slug' => 'lounge', 'specialty' => 'brand', 'is_active' => true]);
        $date = now()->addDays(3)->toDateString();
        ConsultationSlot::create(['consultant_id' => $area->id, 'consultation_date' => $date, 'time_slot' => '13:00', 'total_slots' => 2, 'reserved_slots' => 1]);
        $consultation = Consultation::create([
            'reference' => 'DA-MINE', 'agency_id' => $this->agency->id, 'consultant_id' => $area->id, 'consultant_name' => 'Snapshot Consultant', 'requester_name' => 'My Guest',
            'consultation_date' => $date, 'time_slot' => '13:00', 'companies' => 1,
        ]);

        $this->actingAs($this->staff)
            ->patch(route('admin.consultations.status', $consultation), ['status' => 'cancelled'])
            ->assertRedirect();

        $this->assertSame(Consultation::STATUS_CANCELLED, $consultation->fresh()->status);
        $this->assertSame(0, ConsultationSlot::firstOrFail()->reserved_slots);
    }

    public function test_staff_cannot_open_or_answer_another_agencies_handover(): void
    {
        $handover = $this->foreignHandover();

        $this->actingAs($this->staff)->get(route('admin.handovers.show', $handover))->assertNotFound();
        $this->actingAs($this->staff)->post(route('admin.handovers.reply', $handover), ['message' => 'Hi'])->assertNotFound();
        $this->actingAs($this->staff)->post(route('admin.handovers.resolve', $handover))->assertNotFound();
    }

    public function test_resolving_a_handover_hands_the_conversation_back_to_the_strategist(): void
    {
        $conversation = Conversation::create(['agency_id' => $this->agency->id, 'guest_token' => (string) Str::uuid(), 'locale' => 'en', 'status' => Conversation::STATUS_HANDED_OVER]);
        $handover = HandoverRequest::create(['conversation_id' => $conversation->id, 'reason' => 'strategy_workshop', 'summary' => 'Birthday for 20']);

        $this->actingAs($this->staff)->post(route('admin.handovers.reply', $handover), ['message' => 'Happy to help!'])->assertRedirect();
        $this->assertSame('Happy to help!', ConversationMessage::where('role', ConversationMessage::ROLE_STAFF)->firstOrFail()->content);

        $this->actingAs($this->staff)->post(route('admin.handovers.resolve', $handover))->assertRedirect(route('admin.handovers.index'));

        $this->assertSame(HandoverRequest::STATUS_RESOLVED, $handover->fresh()->status);
        $this->assertSame(Conversation::STATUS_ACTIVE, $conversation->fresh()->status);
    }

    public function test_staff_manage_the_catalog_of_their_own_agency_only(): void
    {
        $this->actingAs($this->staff)->post(route('admin.service-packages.store'), [
            'category' => 'branding', 'name' => 'Brand Essentials', 'price' => 34000, 'tags' => 'Local Business, Growth', 'requirements' => 'brand_brief', 'is_active' => '1',
        ])->assertRedirect(route('admin.service-packages.index'));

        $item = $this->agency->servicePackages()->firstOrFail();
        $this->assertSame('brand-essentials', $item->slug);
        $this->assertSame(['local_business', 'growth'], $item->tags);
        $this->assertTrue($item->is_active);

        $foreign = $this->otherAgency->servicePackages()->create(['category' => 'branding', 'name' => 'Foreign', 'slug' => 'foreign', 'price' => 1]);

        $this->actingAs($this->staff)->get(route('admin.service-packages.edit', $foreign))->assertNotFound();
        $this->actingAs($this->staff)->delete(route('admin.service-packages.destroy', $foreign))->assertNotFound();
        $this->assertNotNull($foreign->fresh());
    }

    public function test_consultant_capacity_is_validated(): void
    {
        $this->actingAs($this->staff)->post(route('admin.consultants.store'), [
            'name' => 'Patio', 'specialty' => 'growth', 'min_companies' => 6, 'max_companies' => 2, 'consultation_fee' => 0,
        ])->assertSessionHasErrors('max_companies');

        $this->actingAs($this->staff)->post(route('admin.consultants.store'), [
            'name' => 'Patio', 'specialty' => 'growth', 'min_companies' => 1, 'max_companies' => 1, 'consultation_fee' => 0, 'features' => 'Pet Friendly, wifi', 'is_active' => '1',
        ])->assertRedirect(route('admin.consultants.index'));

        $this->assertSame(['pet_friendly', 'wifi'], $this->agency->consultants()->firstOrFail()->features);
    }

    public function test_the_dashboard_counts_only_the_staffs_own_agency(): void
    {
        $this->foreignHandover();
        $this->foreignConsultation();

        $this->actingAs($this->staff)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertViewHas('stats', fn (array $stats) => $stats['open_handovers'] === 0 && $stats['pending_consultations'] === 0);
    }

    public function test_an_account_without_a_agency_is_turned_away(): void
    {
        $this->actingAs(User::factory()->create())->get(route('admin.dashboard'))->assertForbidden();
    }
}

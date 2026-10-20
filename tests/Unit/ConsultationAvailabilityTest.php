<?php

namespace Tests\Unit;

use App\Models\Consultant;
use App\Modules\Consultation\Support\ConsultationAvailability;
use Tests\TestCase;

class ConsultationAvailabilityTest extends TestCase
{
    public function test_a_slot_is_a_sixty_minute_range(): void
    {
        $this->assertSame('11:00–12:00', ConsultationAvailability::slotRange('11:00'));
        $this->assertSame('21:00–22:00', ConsultationAvailability::slotRange('21:00'));
    }

    public function test_only_one_company_or_requester_fits_a_consultant_slot(): void
    {
        $consultant = new Consultant(['min_companies' => 1, 'max_companies' => 1]);
        $availability = new ConsultationAvailability;
        $this->assertFalse($availability->fitsParty($consultant, 0));
        $this->assertTrue($availability->fitsParty($consultant, 1));
        $this->assertFalse($availability->fitsParty($consultant, 2));
        $this->assertFalse($availability->fitsParty($consultant, 8));
    }
}

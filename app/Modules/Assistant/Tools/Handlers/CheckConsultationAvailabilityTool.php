<?php

namespace App\Modules\Assistant\Tools\Handlers;

use App\Modules\Assistant\Tools\Support\CatalogSummaries;
use App\Modules\Assistant\Tools\Support\ConsultationInput;
use App\Modules\Assistant\Tools\Tool;
use App\Modules\Assistant\Tools\ToolContext;
use App\Modules\Assistant\Tools\ToolResult;
use App\Modules\Consultation\Support\ConsultationAvailability;

final class CheckConsultationAvailabilityTool implements Tool
{
    public function __construct(
        private readonly ConsultationAvailability $availability,
        private readonly ConsultationInput $input,
    ) {}

    public function name(): string
    {
        return 'check_consultation_availability';
    }

    public function definition(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => $this->name(),
                'description' => 'Check current availability and estimated consultation fee for one consultant and a 60-minute slot. Availability comes only from this tool. One company or requester per slot; schedule requires human confirmation.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'consultant_slug' => ['type' => 'string'],
                        'date' => ['type' => 'string', 'description' => 'YYYY-MM-DD'],
                        'time_slot' => ['type' => 'string', 'description' => 'Slot start time, HH:MM'],
                        'companies' => ['type' => 'integer', 'enum' => [1], 'description' => 'Exactly one company or requester per consultant slot'],
                    ],
                    'required' => ['consultant_slug', 'date', 'time_slot'],
                ],
            ],
        ];
    }

    public function execute(ToolContext $context, array $arguments): ToolResult
    {
        $agency = $context->agency;
        $area = CatalogSummaries::consultantBySlug($agency, (string) ($arguments['consultant_slug'] ?? ''));

        if (! $area) {
            return new ToolResult('Consultant not found.');
        }

        $date = $this->input->parseDate($agency, (string) ($arguments['date'] ?? ''));
        $slot = (string) ($arguments['time_slot'] ?? '');

        if (! $date) {
            return new ToolResult('date must be a valid YYYY-MM-DD date.');
        }

        if ($error = $this->input->dateProblem($agency, $date)) {
            return new ToolResult($error);
        }

        if (! $this->input->isKnownSlot($agency, $slot)) {
            return new ToolResult($this->input->unknownSlotMessage($agency));
        }

        $companies = isset($arguments['companies']) ? (int) $arguments['companies'] : null;

        if ($companies !== null && ! $this->availability->fitsParty($area, $companies)) {
            return new ToolResult('A consultant slot accepts exactly one company or requester. Use human handover for another arrangement.');
        }

        $quote = $this->availability->isSlotInPast($agency, $date, $slot)
            ? null
            : $this->availability->quote($area, $date, $slot);

        if (! $quote) {
            $alternatives = array_map(
                fn (string $s) => ['time_slot' => $s, 'time_range' => ConsultationAvailability::slotRange($s)],
                $this->availability->availableSlots($area, $date),
            );

            return ToolResult::json([
                'available' => false,
                'consultant_slug' => $area->slug,
                'message' => 'No free consultation slot in this consultant for that slot.',
                'other_available_slots_that_day' => $alternatives,
            ], ['type' => 'availability', 'available' => false, 'consultant_slug' => $area->slug]);
        }

        $result = [
            'available' => true,
            'consultant_slug' => $area->slug,
            'name' => $area->translatedName($context->locale),
            'date' => $quote['date'],
            'time_slot' => $quote['time_slot'],
            'time_range' => $quote['time_range'],
            'available_slots' => $quote['available_slots'],
            'consultation_fee' => $quote['fee'],
            'minimum_budget' => $quote['minimum_budget'],
            'currency' => $agency->currency,
        ];

        return ToolResult::json($result, ['type' => 'availability', 'available' => true, 'quote' => $result]);
    }
}

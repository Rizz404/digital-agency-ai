<?php

namespace App\Modules\Assistant\Tools\Handlers;

use App\Models\Consultant;
use App\Modules\Assistant\Tools\Support\CatalogSummaries;
use App\Modules\Assistant\Tools\Support\ConsultationInput;
use App\Modules\Assistant\Tools\Tool;
use App\Modules\Assistant\Tools\ToolContext;
use App\Modules\Assistant\Tools\ToolResult;
use App\Modules\Consultation\Support\ConsultationAvailability;

final class SearchTeamTool implements Tool
{
    public function __construct(
        private readonly ConsultationAvailability $availability,
        private readonly ConsultationInput $input,
    ) {}

    public function name(): string
    {
        return 'search_team';
    }

    public function definition(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => $this->name(),
                'description' => 'Find consultants for one company or requester on a given date, optionally filtered by specialty and time. Returns real available 60-minute slots. Use check_consultation_availability before a request.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'date' => ['type' => 'string', 'description' => 'Consultation date, YYYY-MM-DD'],
                        'companies' => ['type' => 'integer', 'enum' => [1], 'description' => 'Exactly one company or requester per consultant slot'],
                        'time_slot' => ['type' => 'string', 'description' => 'Optional slot start time, HH:MM, one of the consultation slots'],
                        'specialty' => ['type' => 'string', 'enum' => Consultant::specialties(), 'description' => 'Optional preference'],
                    ],
                    'required' => ['date', 'companies'],
                ],
            ],
        ];
    }

    public function execute(ToolContext $context, array $arguments): ToolResult
    {
        $agency = $context->agency;
        $date = $this->input->parseDate($agency, (string) ($arguments['date'] ?? ''));

        if (! $date) {
            return new ToolResult('date must be a valid YYYY-MM-DD date.');
        }

        if ($error = $this->input->dateProblem($agency, $date)) {
            return new ToolResult($error);
        }

        $companies = (int) ($arguments['companies'] ?? 0);
        $slot = $arguments['time_slot'] ?? null;
        $areaType = $arguments['specialty'] ?? null;

        if ($slot && ! $this->input->isKnownSlot($agency, (string) $slot)) {
            return new ToolResult($this->input->unknownSlotMessage($agency));
        }

        $matches = [];

        foreach ($agency->consultants()->where('is_active', true)->with('images')->orderBy('sort_order')->get() as $area) {
            if (! $this->availability->fitsParty($area, $companies) || ($areaType && $area->specialty !== $areaType)) {
                continue;
            }

            $slots = $this->availability->availableSlots($area, $date);

            if ($slot) {
                $slots = array_values(array_intersect($slots, [$slot]));
            }

            if ($slots === []) {
                continue;
            }

            $matches[] = [
                'consultant_slug' => $area->slug,
                ...CatalogSummaries::consultant($area, $agency, $context->locale),
                'date' => $date->toDateString(),
                'available_slots' => array_map(fn (string $s) => ['time_slot' => $s, 'time_range' => ConsultationAvailability::slotRange($s)], $slots),
            ];
        }

        if ($matches === []) {
            return new ToolResult('No consultant has a free consultation slot for that date, party size and time. Tell the guest honestly and offer a different time or date, or call request_human_handover for a large group.');
        }

        return ToolResult::json($matches, [
            'type' => 'team_results',
            'date' => $date->toDateString(),
            'companies' => $companies,
            'areas' => $matches,
        ]);
    }
}

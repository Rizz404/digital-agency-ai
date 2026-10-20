<?php

namespace App\Modules\Assistant\Tools\Handlers;

use App\Models\Consultation;
use App\Modules\Assistant\Tools\Support\CatalogSummaries;
use App\Modules\Assistant\Tools\Support\ConsultationInput;
use App\Modules\Assistant\Tools\Tool;
use App\Modules\Assistant\Tools\ToolContext;
use App\Modules\Assistant\Tools\ToolResult;
use App\Modules\Consultation\Actions\CreateConsultationRequest;
use App\Modules\Consultation\Support\ConsultationAvailability;
use App\Modules\Consultation\Support\ConsultationHandover;
use Carbon\CarbonImmutable;

/**
 * Only ever creates a pending request for the agency to confirm; it never
 * confirms a table and takes no payment.
 */
final class CreateConsultationRequestTool implements Tool
{
    public function __construct(
        private readonly ConsultationAvailability $availability,
        private readonly ConsultationInput $input,
        private readonly CreateConsultationRequest $create,
        private readonly ConsultationHandover $handover,
    ) {}

    public function name(): string
    {
        return 'create_consultation_request';
    }

    public function definition(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => $this->name(),
                'description' => 'Create one pending 60-minute consultation request after the requester confirms the complete summary: consultant, date, time, one company/requester, name and contact. Rechecks capacity. No automatic payment. Only the agency team can confirm the appointment.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'consultant_slug' => ['type' => 'string'],
                        'date' => ['type' => 'string', 'description' => 'YYYY-MM-DD'],
                        'time_slot' => ['type' => 'string', 'description' => 'Slot start time, HH:MM'],
                        'companies' => ['type' => 'integer', 'enum' => [1], 'description' => 'Exactly one company or requester per consultant slot'],
                        'occasion' => ['type' => 'string', 'enum' => Consultation::OCCASIONS],
                        'requester_name' => ['type' => 'string'],
                        'requester_phone' => ['type' => 'string', 'description' => 'Phone or WhatsApp number. Required unless requester_email is given.'],
                        'requester_email' => ['type' => 'string', 'description' => 'Required unless requester_phone is given.'],
                        'notes' => ['type' => 'string', 'description' => 'Special request, at most 500 characters'],
                    ],
                    'required' => ['consultant_slug', 'date', 'time_slot', 'companies', 'requester_name'],
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

        if ($problem = $this->input->guestProblem($arguments)) {
            return new ToolResult($problem);
        }

        $companies = (int) ($arguments['companies'] ?? 0);

        if (! $this->availability->fitsParty($area, $companies)) {
            return new ToolResult('A consultant slot accepts exactly one company or requester. Use human handover for another arrangement.');
        }

        if ($this->availability->isSlotInPast($agency, $date, $slot)) {
            return new ToolResult('That time slot has already started. Ask the guest for a later slot.');
        }

        $guestName = trim((string) $arguments['requester_name']);

        if ($existing = $this->alreadyRequested($context, $area->id, $date, $slot, $companies, $guestName)) {
            return $this->confirmation($context, $existing, alreadyRequested: true);
        }

        $occasion = in_array($arguments['occasion'] ?? null, Consultation::OCCASIONS, true) ? $arguments['occasion'] : null;
        $phone = trim((string) ($arguments['requester_phone'] ?? '')) ?: null;
        $email = trim((string) ($arguments['requester_email'] ?? '')) ?: null;

        $consultation = $this->create->handle($agency, $area, [
            'consultation_date' => $date,
            'time_slot' => $slot,
            'companies' => $companies,
            'occasion' => $occasion,
            'requester_name' => $guestName,
            'requester_email' => $email,
            'requester_phone' => $phone,
            'contact_type' => $phone ? 'phone' : 'email',
            'notes' => trim((string) ($arguments['notes'] ?? '')) ?: null,
        ], $context->conversation);

        if (! $consultation) {
            return new ToolResult('No consultation slot is free in that slot any more — availability may have just changed. Call check_consultation_availability again or offer another slot.');
        }

        return $this->confirmation($context, $consultation);
    }

    /**
     * The model may repeat the call when the guest says yes twice; the same
     * request in the same conversation is answered, not booked again.
     */
    private function alreadyRequested(ToolContext $context, int $areaId, CarbonImmutable $date, string $slot, int $companies, string $guestName): ?Consultation
    {
        return Consultation::query()
            ->where('conversation_id', $context->conversation->id)
            ->where('consultant_id', $areaId)
            ->whereDate('consultation_date', $date->toDateString())
            ->where('time_slot', $slot)
            ->where('companies', $companies)
            ->where('requester_name', $guestName)
            ->where('status', Consultation::STATUS_PENDING)
            ->first();
    }

    private function confirmation(ToolContext $context, Consultation $consultation, bool $alreadyRequested = false): ToolResult
    {
        $area = $consultation->consultant;

        $payload = [
            'consultation_reference' => $consultation->reference,
            'consultant_slug' => $area->slug,
            'name' => $area->translatedName($context->locale),
            'date' => $consultation->consultation_date->toDateString(),
            'time_slot' => $consultation->time_slot,
            'time_range' => ConsultationAvailability::slotRange($consultation->time_slot),
            'companies' => $consultation->companies,
            'consultation_total' => (float) $consultation->consultation_total,
            'currency' => $context->agency->currency,
            'status' => 'pending_confirmation',
        ];

        return ToolResult::json(
            $alreadyRequested ? [...$payload, 'note' => 'This request was already created earlier in this conversation; do not book again.'] : $payload,
            [
                'type' => 'consultation_confirmation',
                'consultation' => $payload,
                'handover' => $this->handover->forConsultation($context->agency, $consultation, $context->locale),
            ],
        );
    }
}

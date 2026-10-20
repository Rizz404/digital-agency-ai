<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Middleware\ResolveAgency;
use App\Http\Requests\Api\QuoteConsultationRequest;
use App\Http\Requests\Api\StoreConsultationRequest;
use App\Models\Agency;
use App\Models\Consultant;
use App\Models\Conversation;
use App\Modules\Consultation\Actions\CreateConsultationRequest;
use App\Modules\Consultation\Actions\ResolveConsultationTarget;
use App\Modules\Consultation\Queries\FindAlternatives;
use App\Modules\Consultation\Support\ConsultationAvailability;
use App\Modules\Consultation\Support\ConsultationHandover;
use App\Modules\Consultation\Validation\ConsultationRules;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;

class ConsultationController extends Controller
{
    public function __construct(
        private readonly ConsultationAvailability $availability,
        private readonly ResolveConsultationTarget $resolveTarget,
        private readonly FindAlternatives $findAlternatives,
    ) {}

    public function quote(QuoteConsultationRequest $request): JsonResponse
    {
        $agency = ResolveAgency::agency($request);
        $locale = ResolveAgency::locale($request);

        $data = $request->validated();
        [$area, $date] = $this->resolveTarget->handle($agency, $data, $locale);

        $quote = $this->availability->quote($area, $date, $data['time_slot']);

        if (! $quote) {
            return $this->unavailable($agency, $area, $date, $data, $locale);
        }

        return response()->json([
            'available' => true,
            'date' => $quote['date'],
            'time_slot' => $quote['time_slot'],
            'time_range' => $quote['time_range'],
            'available_slots' => $quote['available_slots'],
            'fee' => $quote['fee'],
            'minimum_budget' => $quote['minimum_budget'],
            'currency' => $agency->currency,
        ]);
    }

    public function store(
        StoreConsultationRequest $request,
        CreateConsultationRequest $create,
        ConsultationHandover $handover,
    ): JsonResponse {
        $agency = ResolveAgency::agency($request);
        $locale = ResolveAgency::locale($request);

        $data = $request->validated();
        $isEmail = $request->isEmailContact();

        [$area, $date] = $this->resolveTarget->handle($agency, $data, $locale);

        $conversation = isset($data['guest_token'])
            ? Conversation::where('agency_id', $agency->id)->where('guest_token', $data['guest_token'])->first()
            : null;

        $consultation = $create->handle($agency, $area, [
            'consultation_date' => $date,
            'time_slot' => $data['time_slot'],
            'companies' => (int) $data['companies'],
            'occasion' => $data['occasion'] ?? null,
            'requester_name' => $data['requester_name'],
            'requester_email' => $isEmail ? $data['contact_value'] : null,
            'requester_phone' => $isEmail ? null : $data['contact_value'],
            'contact_type' => $data['contact_type'],
            'notes' => $data['special_request'] ?? null,
        ], $conversation);

        if (! $consultation) {
            return $this->unavailable($agency, $area, $date, $data, $locale);
        }

        return response()->json([
            'reference' => $consultation->reference,
            'status' => $consultation->status,
            'fee' => (float) $consultation->consultation_total,
            'currency' => $agency->currency,
            'handover' => $handover->forConsultation($agency, $consultation, $locale),
        ], 201);
    }

    /**
     * No table is free in that area and slot: say so, and offer what is.
     *
     * @param  array<string, mixed>  $data
     */
    private function unavailable(Agency $agency, Consultant $requested, CarbonImmutable $date, array $data, string $locale): JsonResponse
    {
        $message = ConsultationRules::message('unavailable', $locale);

        return response()->json([
            'message' => $message,
            'errors' => ['consultant_slug' => [$message]],
            'alternatives' => $this->findAlternatives->handle($agency, $requested, $date, $data['time_slot'], (int) $data['companies'], $locale),
            'currency' => $agency->currency,
        ], 422);
    }
}

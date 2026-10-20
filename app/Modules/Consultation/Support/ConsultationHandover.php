<?php

namespace App\Modules\Consultation\Support;

use App\Models\Agency;
use App\Models\Consultation;

/**
 * Builds the WhatsApp / phone / email hand-over links that carry a
 * consultation summary (or a plain "talk to staff" request) to the agency team.
 */
class ConsultationHandover
{
    /**
     * @return array{whatsapp_url: ?string, phone_url: ?string, email_url: ?string}
     */
    public function forConsultation(Agency $agency, Consultation $consultation, string $locale): array
    {
        $message = $this->consultationMessage($consultation, $locale);

        return $this->links($agency, $message, $this->subject('consultation', $locale).' '.$consultation->reference);
    }

    /**
     * @return array{whatsapp_url: ?string, phone_url: ?string, email_url: ?string}
     */
    public function forStaff(Agency $agency, string $locale): array
    {
        return $this->links($agency, $this->subject('staff_message', $locale), $this->subject('staff', $locale));
    }

    /**
     * @return array{whatsapp_url: ?string, phone_url: ?string, email_url: ?string}
     */
    private function links(Agency $agency, string $message, string $subject): array
    {
        $whatsapp = preg_replace('/\D+/', '', (string) $agency->whatsapp);
        $phone = preg_replace('/[^\d+]/', '', (string) $agency->phone);

        return [
            'whatsapp_url' => $whatsapp !== '' ? 'https://wa.me/'.$whatsapp.'?text='.rawurlencode($message) : null,
            'phone_url' => $phone !== '' ? 'tel:'.$phone : null,
            'email_url' => filled($agency->email) ? 'mailto:'.$agency->email.'?subject='.rawurlencode($subject).'&body='.rawurlencode($message) : null,
        ];
    }

    private function consultationMessage(Consultation $consultation, string $locale): string
    {
        $consultation->loadMissing('consultant');

        $label = fn (string $key): string => trans('handover.message.'.$key, [], $locale);

        return implode("\n", [
            $label('intro'),
            '',
            $label('name').": {$consultation->requester_name}",
            $label('date').': '.$consultation->consultation_date->locale($locale)->translatedFormat('l, j F Y'),
            $label('time').': '.ConsultationAvailability::slotRange($consultation->time_slot),
            $label('companies').": {$consultation->companies}",
            $label('team').': '.$consultation->consultant_name,
            $label('occasion').': '.($consultation->occasion ?: '-'),
            $label('special').': '.($consultation->notes ?: '-'),
            '',
            $label('reference').": {$consultation->reference}",
        ]);
    }

    private function subject(string $key, string $locale): string
    {
        return trans('handover.subject.'.$key, [], $locale);
    }
}

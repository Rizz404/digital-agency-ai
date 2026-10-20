<?php

namespace App\Modules\Consultation\Validation;

use App\Models\Agency;
use App\Models\Consultation;
use Illuminate\Validation\Rule;

/**
 * The one set of consultation rules, shared by the guest-facing wizard's form
 * requests so the quote step and the final submission can never disagree.
 */
final class ConsultationRules
{
    public const PHONE_PATTERN = '/^\+?[0-9\s\-().]{6,20}$/';

    public const CONTACT_TYPES = ['whatsapp', 'phone', 'email'];

    /**
     * What the guest asks for: where, when and how many.
     *
     * @return array<string, mixed>
     */
    public static function slot(Agency $agency): array
    {
        $today = now($agency->timezone)->toDateString();

        return [
            'consultant_slug' => ['required', 'string', 'max:120'],
            'consultation_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:'.$today],
            'time_slot' => ['required', 'string', 'regex:/^\d{2}:\d{2}$/'],
            'companies' => ['required', 'integer', 'in:1'],
            'occasion' => ['nullable', Rule::in(Consultation::OCCASIONS)],
            'locale' => ['nullable', Rule::in(config('agency.locales'))],
        ];
    }

    /**
     * Who is asking, and how to reach them.
     *
     * @return array<string, mixed>
     */
    public static function guest(): array
    {
        return [
            'requester_name' => ['required', 'string', 'max:100'],
            'contact_type' => ['required', Rule::in(self::CONTACT_TYPES)],
            'contact_value' => ['required', 'string', 'max:120'],
            'special_request' => ['nullable', 'string', 'max:500'],
            'guest_token' => ['nullable', 'uuid'],
        ];
    }

    /**
     * Guest-facing wording: every generic rule failure reads the same.
     *
     * @return array<string, string>
     */
    public static function messages(string $locale): array
    {
        $messages = trans('consultation.messages', [], $locale);

        return [
            'required' => $messages['invalid'],
            'date_format' => $messages['invalid'],
            'integer' => $messages['invalid'],
            'min' => $messages['invalid'],
            'max' => $messages['invalid'],
            'regex' => $messages['invalid'],
            'in' => $messages['invalid'],
            'uuid' => $messages['invalid'],
            'after_or_equal' => $messages['date_past'],
        ];
    }

    /**
     * The guest-facing message for a domain failure, e.g. "capacity".
     *
     * @param  array<string, string>  $replace
     */
    public static function message(string $key, string $locale, array $replace = []): string
    {
        return trans('consultation.messages.'.$key, $replace, $locale);
    }
}

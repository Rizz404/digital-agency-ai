<?php

namespace App\Models;

use App\Modules\Consultation\Enums\ConsultationStatus;
use App\Modules\Consultation\Enums\Occasion;
use App\Modules\Consultation\Support\ConsultationAvailability;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'reference',
    'agency_id',
    'consultant_id',
    'conversation_id',
    'consultant_name',
    'duration_minutes',
    'requester_name',
    'requester_email',
    'requester_phone',
    'contact_type',
    'consultation_date',
    'time_slot',
    'companies',
    'occasion',
    'consultation_total',
    'status',
    'notes',
])]
class Consultation extends Model
{
    use HasFactory;

    public const STATUS_PENDING = ConsultationStatus::Pending->value;

    public const STATUS_CONFIRMED = ConsultationStatus::Confirmed->value;

    public const STATUS_CANCELLED = ConsultationStatus::Cancelled->value;

    /**
     * Occasions a guest can mention so the team can prepare the table.
     */
    public const OCCASIONS = [
        Occasion::BrandLaunch->value,
        Occasion::Website->value,
        Occasion::Campaign->value,
        Occasion::Discovery->value,
        Occasion::Automation->value,
    ];

    protected function casts(): array
    {
        return [
            'consultation_date' => 'date',
            'consultation_total' => 'decimal:2',
        ];
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    public function consultant(): BelongsTo
    {
        return $this->belongsTo(Consultant::class)->withTrashed();
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * "11:00–12:00" for a consultation that starts at 11:00.
     */
    public function getTimeRangeAttribute(): string
    {
        return ConsultationAvailability::slotRange($this->time_slot);
    }

    /**
     * A short, unambiguous, non-sequential code companies can quote to staff.
     */
    public static function generateReference(): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

        do {
            $reference = 'DA-'.collect(range(1, 6))
                ->map(fn () => $alphabet[random_int(0, strlen($alphabet) - 1)])
                ->implode('');
        } while (static::where('reference', $reference)->exists());

        return $reference;
    }
}

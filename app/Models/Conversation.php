<?php

namespace App\Models;

use App\Modules\Conversation\Enums\ConversationStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'agency_id',
    'guest_token',
    'requester_name',
    'requester_email',
    'locale',
    'status',
    'handover_summary',
    'current_scene',
    'selected_service_package_id',
    'selected_consultant_id',
    'selected_facility_id',
    'consultation_state',
    'last_message_at',
])]
class Conversation extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = ConversationStatus::Active->value;

    public const STATUS_HANDED_OVER = ConversationStatus::HandedOver->value;

    public const STATUS_CLOSED = ConversationStatus::Closed->value;

    /**
     * The UI scenes the guest can be in while talking to the AI Digital Strategist.
     */
    public const SCENES = ['home', 'catalog', 'service_package', 'team', 'consultant', 'facilities', 'facility_detail', 'consultation', 'handover'];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
            'consultation_state' => 'array',
        ];
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    public function selectedServicePackage(): BelongsTo
    {
        return $this->belongsTo(ServicePackage::class, 'selected_service_package_id');
    }

    public function selectedConsultant(): BelongsTo
    {
        return $this->belongsTo(Consultant::class, 'selected_consultant_id');
    }

    public function selectedFacility(): BelongsTo
    {
        return $this->belongsTo(AgencyKnowledgeItem::class, 'selected_facility_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ConversationMessage::class)->orderBy('created_at');
    }

    public function consultations(): HasMany
    {
        return $this->hasMany(Consultation::class);
    }

    public function handoverRequest(): HasOne
    {
        return $this->hasOne(HandoverRequest::class)->latestOfMany();
    }

    public function isHandedOver(): bool
    {
        return $this->status === self::STATUS_HANDED_OVER;
    }
}

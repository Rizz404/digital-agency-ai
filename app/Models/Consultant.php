<?php

namespace App\Models;

use App\Modules\Team\Enums\Specialty;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'agency_id',
    'name',
    'slug',
    'description',
    'translations',
    'specialty',
    'min_companies',
    'max_companies',
    'features',
    'consultation_fee',
    'minimum_budget',
    'is_active',
    'sort_order',
])]
class Consultant extends Model
{
    use HasFactory, SoftDeletes;

    public const TYPE_BRAND = Specialty::Brand->value;

    public const TYPE_GROWTH = Specialty::Growth->value;

    public const TYPE_DIGITAL = Specialty::Digital->value;

    public const TYPE_STRATEGY = Specialty::Strategy->value;

    /**
     * @return list<string>
     */
    public static function specialties(): array
    {
        return Specialty::values();
    }

    protected function casts(): array
    {
        return [
            'translations' => 'array',
            'features' => 'array',
            'consultation_fee' => 'decimal:2',
            'minimum_budget' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ConsultantImage::class)->orderBy('sort_order');
    }

    public function inventory(): HasMany
    {
        return $this->hasMany(ConsultationSlot::class);
    }

    public function translatedName(string $locale): string
    {
        $value = $this->translations[$locale]['name'] ?? null;

        return filled($value) ? $value : $this->name;
    }

    public function translatedDescription(string $locale): ?string
    {
        $value = $this->translations[$locale]['description'] ?? null;

        return filled($value) ? $value : $this->description;
    }
}

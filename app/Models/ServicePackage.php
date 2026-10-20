<?php

namespace App\Models;

use App\Modules\Catalog\Enums\ServiceCategory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * One service package on the agency's catalog. The price here is the only price the
 * AI Digital Strategist may ever quote, and only after reading it through a tool call.
 */
#[Fillable([
    'agency_id',
    'category',
    'name',
    'slug',
    'description',
    'translations',
    'price',
    'image_url',
    'tags',
    'requirements',
    'delivery',
    'estimated_days',
    'is_featured',
    'is_sold_out',
    'is_active',
    'sort_order',
])]
class ServicePackage extends Model
{
    use HasFactory, SoftDeletes;

    public const CATEGORY_BRANDING = ServiceCategory::Branding->value;

    public const CATEGORY_WEB_DESIGN = ServiceCategory::WebDesign->value;

    public const CATEGORY_PERFORMANCE_ADS = ServiceCategory::PerformanceAds->value;

    public const CATEGORY_CONTENT = ServiceCategory::Content->value;

    public const CATEGORY_SEO = ServiceCategory::Seo->value;

    public const CATEGORY_AUTOMATION = ServiceCategory::Automation->value;

    /**
     * Tags a guest can filter on when they have a service_tag need.
     */
    public const SERVICE_TAGS = ['startup', 'growth', 'enterprise', 'ecommerce', 'local_business'];

    /**
     * @return list<string>
     */
    public static function categories(): array
    {
        return ServiceCategory::values();
    }

    protected function casts(): array
    {
        return [
            'translations' => 'array',
            'tags' => 'array',
            'requirements' => 'array',
            'price' => 'decimal:2',
            'is_featured' => 'boolean',
            'is_sold_out' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    /**
     * The picture to show: a full URL as stored, or a file under public/.
     */
    public function getImageSourceAttribute(): ?string
    {
        if (blank($this->image_url)) {
            return null;
        }

        return str_starts_with($this->image_url, 'http') ? $this->image_url : asset($this->image_url);
    }

    public function hasTag(string $tag): bool
    {
        return in_array($tag, $this->tags ?? [], true);
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

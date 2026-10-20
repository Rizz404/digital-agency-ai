<?php

namespace App\Modules\Catalog\Actions;

use App\Models\Agency;
use App\Models\ServicePackage;
use Illuminate\Support\Str;

/**
 * Creates a catalog item or updates one. A new item gets a slug from its name; an
 * existing one keeps its slug so guest links never break.
 */
class SaveServicePackage
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(Agency $agency, array $attributes, ?ServicePackage $servicePackage = null): ServicePackage
    {
        if ($servicePackage) {
            $servicePackage->update($attributes);

            return $servicePackage;
        }

        return $agency->servicePackages()->create([...$attributes, 'slug' => $this->uniqueSlug($agency, $attributes['name'])]);
    }

    private function uniqueSlug(Agency $agency, string $name): string
    {
        $base = Str::slug($name) ?: 'item';
        $slug = $base;
        $suffix = 1;

        while ($agency->servicePackages()->withTrashed()->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}

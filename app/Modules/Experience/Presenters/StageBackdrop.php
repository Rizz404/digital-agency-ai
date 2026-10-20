<?php

namespace App\Modules\Experience\Presenters;

/**
 * Artwork for a scene: an illustrated background, the strategist cut-out and
 * where she stands, so the scene's text and panels stay clear of her.
 */
final class StageBackdrop
{
    /**
     * @return array{image: string, focus: string, focusMobile: string, character: string, anchor: string, text: string, avatarImage: string}
     */
    public static function for(string $scene): array
    {
        $scenes = config('agency.scenes');
        $artwork = $scenes[config('agency.scene_artwork')[$scene] ?? $scene] ?? $scenes['home'];

        return [
            'image' => asset($artwork['image']),
            'focus' => $artwork['focus'],
            'focusMobile' => $artwork['focusMobile'],
            'character' => asset($artwork['character']),
            'anchor' => $artwork['anchor'],
            'text' => $artwork['text'],
            'avatarImage' => self::avatar(),
        ];
    }

    public static function avatar(): string
    {
        return asset(config('agency.avatar_image'));
    }
}

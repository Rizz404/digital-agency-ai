<?php

return [

    'trusted_proxies' => array_values(array_filter(array_map('trim', explode(',', (string) env('TRUSTED_PROXIES', ''))))),

    'consultation_slots' => ['09:00', '10:00', '11:00', '13:00', '14:00', '15:00', '16:00'],

    /*
    |--------------------------------------------------------------------------
    | Agency experience
    |--------------------------------------------------------------------------
    |
    | Guest-facing settings shared by every agency. Per-agency values (name,
    | currency, opening hours, default locale) live in the agencies table.
    | Provider connection values stay in config/services.php under
    | "local_llm"; AI budgets are in config/assistant.php.
    |
    */

    'locales' => ['id', 'en', 'ja'],

    'default_locale' => 'id',

    /*
    |--------------------------------------------------------------------------
    | Stage artwork
    |--------------------------------------------------------------------------
    |
    | The small round picture of the strategist used beside chat and narration,
    | and the artwork per scene: a plain illustrated background plus one
    | cut-out of the strategist layered in front, so she can stand clear of the
    | panels. Scenes without artwork of their own borrow another scene's.
    |
    */

    'avatar_image' => 'images/agency/avatar.webp',

    'scenes' => [
        'home' => ['image' => 'images/agency/studio-home.webp', 'character' => 'images/agency/strategist.webp', 'focus' => 'center', 'focusMobile' => '55% center', 'anchor' => 'center', 'text' => 'top'],
        'catalog' => ['image' => 'images/agency/studio-catalog.webp', 'character' => 'images/agency/strategist.webp', 'focus' => 'center', 'focusMobile' => '55% center', 'anchor' => 'center', 'text' => 'top'],
        'team' => ['image' => 'images/agency/studio-team.webp', 'character' => 'images/agency/strategist.webp', 'focus' => 'center', 'focusMobile' => '55% center', 'anchor' => 'center', 'text' => 'top'],
        'facilities' => ['image' => 'images/agency/studio-facilities.webp', 'character' => 'images/agency/strategist.webp', 'focus' => 'center', 'focusMobile' => '55% center', 'anchor' => 'center', 'text' => 'top'],
        'info' => ['image' => 'images/agency/studio-info.webp', 'character' => 'images/agency/strategist.webp', 'focus' => 'center', 'focusMobile' => '55% center', 'anchor' => 'center', 'text' => 'top'],
        'staff' => ['image' => 'images/agency/studio-staff.webp', 'character' => 'images/agency/strategist.webp', 'focus' => 'center', 'focusMobile' => '55% center', 'anchor' => 'center', 'text' => 'top'],
        'consultation' => ['image' => 'images/agency/studio-consultation.webp', 'character' => 'images/agency/strategist.webp', 'focus' => 'center', 'focusMobile' => '55% center', 'anchor' => 'center', 'text' => 'top'],
    ],

    'scene_artwork' => [
        'service-package' => 'catalog',
        'consultant' => 'team',
        'facility' => 'facilities',
    ],

];

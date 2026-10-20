@props(['agency', 'locale', 'supportedLocales', 'labels', 'stageCopy', 'narration', 'scene', 'backdrop', 'navItems', 'terms' => [], 'title' => null, 'welcome' => null, 'sidebar' => null, 'nav' => true])
{{-- Agency studio: numbered project index, central canvas and an AI strategy companion. --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $locale) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? $agency->name.' · AI Digital Strategist' }}</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="stage-page antialiased" style="--strategist-image: url('{{ $backdrop['avatarImage'] }}'); --stage-focus: {{ $backdrop['focus'] }}; --stage-focus-mobile: {{ $backdrop['focusMobile'] }}">
    <main class="stage" data-stage data-scene="{{ $scene }}" data-page="{{ $scene }}" data-chat-dock="center">
        <div class="stage-loader" data-stage-loader role="status"><span class="agency-monogram" aria-hidden="true">{{ mb_substr($agency->name, 0, 1) }}</span><p>{{ $stageCopy['loading'] }}</p></div>
        <img src="{{ $backdrop['image'] }}" alt="" class="stage-image" fetchpriority="high">
        <div class="stage-shade"></div>
        <img src="{{ $backdrop['character'] }}" alt="{{ $stageCopy['assistant'] }}" class="stage-character" data-anchor="{{ $backdrop['anchor'] }}">
        <p class="stage-tour" data-stage-tour aria-live="polite"></p>

        <header class="stage-header">
            <a href="{{ route('agency.show', ['agencySlug' => $agency->slug, 'lang' => $locale]) }}" @if($scene !== 'home') data-stage-exit data-tour-line="{{ $narration['tour_home'] }}" @endif class="flex min-w-0 items-center gap-3">
                <span class="agency-monogram" aria-hidden="true">{{ mb_substr($agency->name, 0, 1) }}</span>
                <span class="min-w-0"><span class="block truncate font-semibold tracking-tight">{{ $agency->name }}</span><span class="block truncate text-xs opacity-75">{{ $agency->city }} · {{ $agency->country }}</span></span>
            </a>
            <div class="stage-tools">
                <x-sound-toggle :on="$stageCopy['sound_on']" :off="$stageCopy['sound_off']" />
                <nav aria-label="Language" class="stage-lang">
                    @foreach ($supportedLocales as $code)
                        <a href="?lang={{ $code }}" lang="{{ $code }}" aria-label="{{ ['id' => 'Bahasa Indonesia', 'en' => 'English', 'ja' => '日本語'][$code] }}" @if($code === $locale) aria-current="true" @endif>{{ $code }}</a>
                    @endforeach
                </nav>
            </div>
        </header>

        {{ $welcome }}
        @if ($sidebar)<div class="studio-subindex">{{ $sidebar }}</div>@endif

        <aside class="studio-index" aria-label="{{ $stageCopy['explore'] }}">
            <div class="studio-index-head"><p class="stage-eyebrow">{{ $stageCopy['explore'] }}</p><span aria-hidden="true">DA / 01</span></div>
            <nav class="stage-navigation">
                @foreach ($navItems as $item)
                    <a href="{{ $item['href'] }}" data-stage-exit data-tour-line="{{ $item['tour'] }}"
                       @if($item['key'] === $scene || ($scene === 'service-package' && $item['key'] === 'catalog') || ($scene === 'consultant' && $item['key'] === 'team') || ($scene === 'facility' && $item['key'] === 'facilities')) aria-current="page" @endif>
                        <span class="studio-index-number">0{{ $loop->iteration }}</span>
                        <span>{{ $item['label'] }}</span><span class="studio-index-arrow" aria-hidden="true">↗</span>
                    </a>
                @endforeach
            </nav>
            <p class="studio-index-foot">{{ $stageCopy['studio_note'] }}</p>
        </aside>
        <p class="studio-edition">{{ $stageCopy['demo'] }}</p>

        {{ $slot }}

        <div data-chat-widget>
            @include('agency.chat')
        </div>
        <noscript><p class="stage-noscript">Aktifkan JavaScript untuk menggunakan navigasi dan AI Digital Strategist. {{ $agency->phone }}</p></noscript>
    </main>
</body>
</html>

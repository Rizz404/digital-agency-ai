<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $locale) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Digital Agency AI</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="stage-page antialiased" style="--strategist-image: url('{{ $avatarImage }}'); --stage-focus: {{ $backdrop['focus'] }}; --stage-focus-mobile: {{ $backdrop['focusMobile'] }}">
    <main class="stage" data-stage data-scene="opening">
        <div class="stage-loader" data-stage-loader role="status"><span class="agency-monogram" aria-hidden="true">D</span><p>{{ $opening['loading'] }}</p></div>
        <img src="{{ $backdrop['image'] }}" alt="" class="stage-image" fetchpriority="high">
        <div class="stage-shade"></div>
        <img src="{{ $backdrop['character'] }}" alt="" class="stage-character" data-anchor="center">

        <header class="stage-header">
            <span class="flex min-w-0 items-center gap-3">
                <span class="agency-monogram" aria-hidden="true">D</span>
                <span class="block truncate font-semibold tracking-tight">Digital Agency AI</span>
            </span>
            <div class="stage-tools">
                <x-sound-toggle :on="$opening['sound_on']" :off="$opening['sound_off']" />
                <nav aria-label="Language" class="stage-lang">
                    @foreach ($supportedLocales as $code)
                        <a href="?lang={{ $code }}" lang="{{ $code }}" aria-label="{{ ['id' => 'Bahasa Indonesia', 'en' => 'English', 'ja' => '日本語'][$code] }}" @if($code === $locale) aria-current="true" @endif>{{ $code }}</a>
                    @endforeach
                </nav>
            </div>
        </header>

        <section class="opening-panel" aria-labelledby="opening-title">
            <p class="stage-eyebrow">INDEPENDENT CREATIVE STUDIO / AI</p>
            <h1 id="opening-title">{{ $opening['welcome'] }}<br><em>Digital Agency AI</em></h1>
            <p class="opening-tagline">{{ $opening['tagline'] }}</p>

            @if ($agencies->count() === 1)
                @php
                    $agencySlug = $agencies->first()->slug;
                    $openingLinks = [
                        'catalog' => route('agency.catalog', ['agencySlug' => $agencySlug, 'lang' => $locale]),
                        'team' => route('agency.team', ['agencySlug' => $agencySlug, 'lang' => $locale]),
                        'consultation' => route('agency.consultation', ['agencySlug' => $agencySlug, 'lang' => $locale]),
                        'staff' => route('agency.staff', ['agencySlug' => $agencySlug, 'lang' => $locale]),
                    ];
                @endphp
                <ul class="opening-links">
                    @foreach ($openingLinks as $key => $href)
                        <li><a data-stage-exit href="{{ $href }}"><span>{{ $opening[$key] }}</span><span aria-hidden="true">›</span></a></li>
                    @endforeach
                </ul>
            @endif
        </section>

        <p class="opening-edition">{{ $opening['demo'] }}</p>
        <div class="opening-enter">
            @forelse ($agencies as $agency)
                <a href="{{ route('agency.show', ['agencySlug' => $agency->slug, 'lang' => $locale]) }}" class="opening-button" data-stage-exit>
                    {{ str_replace(':name', $agency->name, $opening['enter']) }} <span aria-hidden="true">→</span>
                </a>
            @empty
                <p class="opening-empty">{{ $opening['empty'] }}</p>
            @endforelse
        </div>
    </main>
</body>
</html>

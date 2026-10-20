<x-agency-stage :agency="$agency" :locale="$locale" :supported-locales="$supportedLocales" :labels="$labels" :stage-copy="$stageCopy" :narration="$narration" :scene="$scene" :backdrop="$backdrop" :nav-items="$navItems" :terms="$terms">
    <section class="stage-content studio-catalog-canvas @container">
        <p class="stage-eyebrow">{{ $stageCopy['explore'] }}</p>
        <h2>{{ $labels['team_heading'] }}</h2>
        @if ($teamNarrations['team'])
            @include('agency.narrator', ['text' => $teamNarrations['team'], 'key' => 'team', 'inline' => true])
        @endif
        <div class="studio-project-grid">
            @forelse ($consultants as $consultant)
                <a class="studio-project-card" href="{{ route('agency.consultant', ['agencySlug' => $agency->slug, 'areaSlug' => $consultant->slug, 'lang' => $locale]) }}" data-stage-exit>
                    @if ($consultant->images->first()?->image_source)<img src="{{ $consultant->images->first()?->image_source }}" alt="" loading="lazy">@endif
                    <div><span class="stage-eyebrow">0{{ $loop->iteration }}</span><h3>{{ $consultant->translatedName($locale) }}</h3><p>{{ $terms['specialty'][$consultant->specialty] ?? $consultant->specialty }} · 60 min</p></div>
                    <span class="studio-card-arrow" aria-hidden="true">↗</span>
                </a>
            @empty
                <p>{{ $stageCopy['team_empty'] }}</p>
            @endforelse
        </div>
    </section>
</x-agency-stage>

<x-agency-stage :agency="$agency" :locale="$locale" :supported-locales="$supportedLocales" :labels="$labels" :stage-copy="$stageCopy" :narration="$narration" :scene="$scene" :backdrop="$backdrop" :nav-items="$navItems" :terms="$terms">
    <section class="stage-content studio-catalog-canvas @container">
        <p class="stage-eyebrow">{{ $stageCopy['explore'] }}</p>
        <h2>{{ $labels['catalog_heading'] }}</h2>
        @if ($catalogNarrations['catalog'])
            @include('agency.narrator', ['text' => $catalogNarrations['catalog'], 'key' => 'catalog', 'inline' => true])
        @endif
        <div class="studio-project-grid">
            @forelse ($servicePackages as $package)
                <a class="studio-project-card" href="{{ route('agency.service-package', ['agencySlug' => $agency->slug, 'itemSlug' => $package->slug, 'lang' => $locale]) }}" data-stage-exit>
                    @if ($package->image_source)<img src="{{ $package->image_source }}" alt="" loading="lazy">@endif
                    <div><span class="stage-eyebrow">0{{ $loop->iteration }}</span><h3>{{ $package->translatedName($locale) }}</h3><p>{{ $agency->currency }} {{ number_format((float) $package->price, 0, ',', '.') }} · {{ $labels['from'] }}</p></div>
                    <span class="studio-card-arrow" aria-hidden="true">↗</span>
                </a>
            @empty
                <p>{{ $stageCopy['catalog_empty'] }}</p>
            @endforelse
        </div>
    </section>
</x-agency-stage>

<x-agency-stage :agency="$agency" :locale="$locale" :supported-locales="$supportedLocales" :labels="$labels" :stage-copy="$stageCopy" :narration="$narration" :scene="$scene" :backdrop="$backdrop" :nav-items="$navItems" :terms="$terms">
    <x-slot:welcome>
        <section class="stage-welcome" aria-labelledby="studio-title">
            <p class="stage-eyebrow">{{ $stageCopy['studio_note'] }}</p>
            <h1 id="studio-title">{{ $stageCopy['welcome'] }}<br><em>{{ $stageCopy['counter'] }}.</em></h1>
            <p>{{ $stageCopy['intro'] }}</p>
            <div class="studio-welcome-actions">
                <a href="{{ route('agency.catalog', ['agencySlug' => $agency->slug, 'lang' => $locale]) }}" data-stage-exit data-tour-line="{{ $narration['tour_catalog'] }}" class="stage-action">{{ $labels['chip_catalog'] }} <span aria-hidden="true">↗</span></a>
                <a href="{{ route('agency.consultation', ['agencySlug' => $agency->slug, 'lang' => $locale]) }}" data-stage-exit data-tour-line="{{ $narration['tour_consultation'] }}" class="studio-text-link">{{ $labels['chip_consultation'] }}</a>
            </div>
            @include('agency.narrator', ['text' => $labels['chat_intro'], 'key' => 'studio-home', 'onStage' => true])
        </section>
    </x-slot:welcome>
</x-agency-stage>

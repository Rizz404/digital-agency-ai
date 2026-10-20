{{-- On the team scenes the side card becomes the area index, so the guest can step between areas without going back. --}}
<aside class="stage-catalog" aria-label="{{ $labels['team_heading'] }}">
    <a href="{{ route('agency.show', ['agencySlug' => $agency->slug, 'lang' => $locale]) }}" data-stage-exit data-tour-line="{{ $narration['tour_home'] }}" class="stage-catalog-back"><span aria-hidden="true">‹</span> {{ $stageCopy['explore'] }}</a>
    <p class="stage-eyebrow">{{ $labels['team_heading'] }}</p>
    <nav class="item-nav">
        @foreach ($consultants as $item)
            <a href="{{ route('agency.consultant', ['agencySlug' => $agency->slug, 'areaSlug' => $item->slug, 'lang' => $locale]) }}"
                data-stage-exit
                @if (($consultant ?? null)?->slug === $item->slug) aria-current="page" @endif>
                @if ($item->images->first())
                    <img class="item-nav-thumb" src="{{ $item->images->first()->image_source }}" alt="" loading="lazy">
                @endif
                <span class="item-nav-text">
                    <span class="item-nav-name">{{ $item->translatedName($locale) }}</span>
                    <span class="item-nav-price">{{ $terms['specialty'][$item->specialty] ?? Str::headline($item->specialty) }} · {{ $item->min_companies > 1 ? $item->min_companies.'–' : '' }}{{ $item->max_companies }} {{ $stageCopy['companies_unit'] }}</span>
                </span>
            </a>
        @endforeach
    </nav>
    <span class="stage-catalog-footer">{{ $stageCopy['available'] }} · DIGITAL AGENCY AI</span>
</aside>

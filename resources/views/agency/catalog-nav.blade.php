{{-- On the catalog scenes the side card becomes the catalog index, so the guest can step between service packages without going back. --}}
<aside class="stage-catalog" aria-label="{{ $labels['catalog_heading'] }}">
    <a href="{{ route('agency.show', ['agencySlug' => $agency->slug, 'lang' => $locale]) }}" data-stage-exit data-tour-line="{{ $narration['tour_home'] }}" class="stage-catalog-back"><span aria-hidden="true">‹</span> {{ $stageCopy['explore'] }}</a>
    <p class="stage-eyebrow">{{ $labels['catalog_heading'] }}</p>
    <nav class="item-nav">
        @foreach ($servicePackages->groupBy('category') as $category => $group)
            <p class="item-nav-heading">{{ $terms['category'][$category] ?? Str::headline($category) }}</p>
            @foreach ($group as $item)
                <a href="{{ route('agency.service-package', ['agencySlug' => $agency->slug, 'itemSlug' => $item->slug, 'lang' => $locale]) }}"
                    data-stage-exit
                    @if (($servicePackage ?? null)?->slug === $item->slug) aria-current="page" @endif>
                    @if ($item->image_source)
                        <img class="item-nav-thumb" src="{{ $item->image_source }}" alt="" loading="lazy">
                    @endif
                    <span class="item-nav-text">
                        <span class="item-nav-name">{{ $item->translatedName($locale) }}</span>
                        <span class="item-nav-price">{{ $agency->currency }} {{ number_format((float) $item->price, 0, ',', '.') }}@if ($item->is_sold_out)<span class="item-nav-badge">{{ $stageCopy['sold_out'] }}</span>@endif</span>
                    </span>
                </a>
            @endforeach
        @endforeach
    </nav>
    <span class="stage-catalog-footer">{{ $stageCopy['available'] }} · DIGITAL AGENCY AI</span>
</aside>

<x-agency-stage :agency="$agency" :locale="$locale" :supported-locales="$supportedLocales" :labels="$labels" :stage-copy="$stageCopy" :narration="$narration" :scene="$scene" :backdrop="$backdrop" :nav-items="$navItems" :terms="$terms" :title="$servicePackage->translatedName($locale).' · '.$agency->name">
    @if($servicePackages->isNotEmpty())
        <x-slot:sidebar>@include('agency.catalog-nav')</x-slot:sidebar>
    @endif

    <div class="stage-panels">
        @include('agency.catalog-scene')
    </div>
</x-agency-stage>

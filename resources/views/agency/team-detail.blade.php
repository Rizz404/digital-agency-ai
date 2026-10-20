<x-agency-stage :agency="$agency" :locale="$locale" :supported-locales="$supportedLocales" :labels="$labels" :stage-copy="$stageCopy" :narration="$narration" :scene="$scene" :backdrop="$backdrop" :nav-items="$navItems" :terms="$terms" :title="$consultant->translatedName($locale).' · '.$agency->name">
    @if($consultants->isNotEmpty())
        <x-slot:sidebar>@include('agency.team-nav')</x-slot:sidebar>
    @endif

    <div class="stage-panels">
        @include('agency.team-scene')
    </div>
</x-agency-stage>

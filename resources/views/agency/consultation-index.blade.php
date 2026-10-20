<x-agency-stage :agency="$agency" :locale="$locale" :supported-locales="$supportedLocales" :labels="$labels" :stage-copy="$stageCopy" :narration="$narration" :scene="$scene" :backdrop="$backdrop" :nav-items="$navItems" :terms="$terms" :nav="false" :title="$labels['consultation_heading'].' · '.$agency->name">
    <div class="stage-panels">
        @include('agency.consultation')
    </div>
</x-agency-stage>

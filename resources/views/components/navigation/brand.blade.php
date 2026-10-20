@props([
    'name' => null,
    'caption' => 'Agency admin',
])

<div {{ $attributes }}>
    <p class="font-display text-base font-semibold text-primary">{{ $name }}</p>
    <p class="text-xs text-muted">{{ $caption }}</p>
</div>

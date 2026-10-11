@props(['title' => 'Belum ada data', 'description' => null])

<div {{ $attributes->class(['empty-state']) }}>
    <span class="empty-state-mark" aria-hidden="true">—</span>
    <p class="empty-state-title">{{ $title }}</p>
    @if ($description)
        <p class="muted">{{ $description }}</p>
    @endif
</div>

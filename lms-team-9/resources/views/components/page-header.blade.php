@props(['title', 'subtitle' => null, 'eyebrow' => 'Sistem Pembelajaran Perkuliahan'])

<header class="page-header">
    <div>
        <p class="eyebrow">{{ $eyebrow }}</p>
        <h1>{{ $title }}</h1>
        @if ($subtitle)
            <p class="page-subtitle">{{ $subtitle }}</p>
        @endif
    </div>
    @if (isset($actions) && $actions->isNotEmpty())
        <div class="page-actions">{{ $actions }}</div>
    @endif
</header>

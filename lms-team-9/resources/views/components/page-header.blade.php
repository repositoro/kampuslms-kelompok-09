@props(['title', 'subtitle' => null])

<header class="page-header">
    <div>
        <h1>{{ $title }}</h1>
        @if ($subtitle)
            <p class="page-subtitle">{{ $subtitle }}</p>
        @endif
    </div>
    @if (isset($actions) && $actions->isNotEmpty())
        <div class="page-actions">{{ $actions }}</div>
    @endif
</header>

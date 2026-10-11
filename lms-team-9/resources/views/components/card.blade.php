@props(['title' => null])

<section {{ $attributes->class(['card']) }}>
    @if ($title)
        <h2 class="card-title">{{ $title }}</h2>
    @endif
    {{ $slot }}
</section>

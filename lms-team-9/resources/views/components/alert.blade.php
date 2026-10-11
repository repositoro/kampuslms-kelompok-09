@props(['variant' => 'info'])

<div role="status" {{ $attributes->class(['alert', 'alert--'.$variant]) }}>
    {{ $slot }}
</div>

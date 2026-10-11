@props(['href' => null])

<a {{ $attributes->class(['brand']) }} href="{{ $href ?? url('/') }}" aria-label="KampusLMS">
    <span class="brand-mark" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none">
            <path d="m2.5 8.8 9.5-5 9.5 5-9.5 5-9.5-5Z" fill="currentColor"/>
            <path d="M6 10.7v5.1c3.8 2.7 8.2 2.7 12 0v-5.1l-6 3.2-6-3.2Z" fill="currentColor"/>
            <path d="M21.5 9v6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
        </svg>
    </span>
    <span>KampusLMS</span>
</a>

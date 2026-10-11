@props(['tabs' => []])

<nav class="tabs" aria-label="Navigasi halaman">
    @foreach ($tabs as $tab)
        @if (Route::has($tab['route']))
            @php($isActive = array_key_exists('active', $tab) ? (bool) $tab['active'] && request()->routeIs($tab['active']) : request()->routeIs($tab['route']))
            <a class="tab-link {{ $isActive ? 'is-active' : '' }}"
               href="{{ route($tab['route'], $tab['parameters'] ?? []) }}{{ isset($tab['anchor']) ? '#'.$tab['anchor'] : '' }}"
               @if ($isActive) aria-current="page" @endif>
                {{ $tab['label'] }}
            </a>
        @endif
    @endforeach
</nav>

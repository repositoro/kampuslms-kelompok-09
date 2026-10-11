<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'KampusLMS' }}</title>
    @vite(['resources/css/app.css'])
</head>
<body>
    @auth
        <div class="app-shell">
            <aside class="sidebar" aria-label="Navigasi utama">
                <x-logo :href="route('dashboard')" />

                <details class="mobile-navigation">
                    <summary>Menu navigasi</summary>
                    <nav class="sidebar-nav">
                        @include('layouts.partials.navigation')
                    </nav>
                </details>

                <nav class="sidebar-nav desktop-navigation">
                    @include('layouts.partials.navigation')
                </nav>

                <div class="sidebar-footer">
                    <span class="user-avatar" aria-hidden="true">{{ mb_substr(auth()->user()->name, 0, 1) }}</span>
                    <span class="user-summary">
                        <strong>{{ auth()->user()->name }}</strong>
                        <span>{{ ucfirst(auth()->user()->role) }}</span>
                    </span>
                </div>
            </aside>

            <div class="app-main">
                <header class="topbar">
                    <div>
                        <p class="eyebrow">Portal pembelajaran</p>
                        <p class="topbar-welcome">Selamat datang, {{ auth()->user()->name }}</p>
                    </div>
                    <div class="topbar-actions">
                        @if (Route::has('notifications.index'))
                            <a class="notification-link" href="{{ route('notifications.index') }}" aria-label="Notifikasi">
                                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none">
                                    <path d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9ZM10 21h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                @if (isset($unreadNotificationsCount) && $unreadNotificationsCount > 0)
                                    <span class="notification-count">{{ $unreadNotificationsCount }}</span>
                                @endif
                            </a>
                        @endif
                        <span class="topbar-user">{{ auth()->user()->name }}</span>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button class="button button--secondary button--small" type="submit">Keluar</button>
                        </form>
                    </div>
                </header>

                <main class="content">
                    @if (session('success'))
                        <x-alert variant="success">{{ session('success') }}</x-alert>
                    @endif
                    @if (session('error'))
                        <x-alert variant="error">{{ session('error') }}</x-alert>
                    @endif
                    @if (session('info'))
                        <x-alert variant="info">{{ session('info') }}</x-alert>
                    @endif
                    {{ $slot }}
                </main>
                <footer class="content"><span>KampusLMS · Sistem Pembelajaran Perkuliahan</span></footer>
            </div>
        </div>
    @else
        <div class="guest-shell">
            <main class="guest-content">
                <x-logo class="guest-brand" :href="url('/')" />
                @if (session('success'))
                    <x-alert variant="success">{{ session('success') }}</x-alert>
                @endif
                @if (session('error'))
                    <x-alert variant="error">{{ session('error') }}</x-alert>
                @endif
                {{ $slot }}
            </main>
        </div>
    @endauth
</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Kampus LMS' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-800 antialiased">

    <nav class="bg-slate-800 text-white">
        <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between">
            <span class="font-semibold text-lg">Kampus LMS</span>

            <div class="flex gap-6 text-sm">
                <a href="{{ route('dashboard') }}"
                   class="hover:text-slate-300 {{ request()->routeIs('dashboard') ? 'font-bold' : '' }}">
                    Dashboard
                </a>
                <a href="{{ route('courses.index') }}"
                   class="hover:text-slate-300 {{ request()->routeIs('courses.*') ? 'font-bold' : '' }}">
                    Mata Kuliah
                </a>
                <a href="{{ route('admin.users.index') }}"
                   class="hover:text-slate-300 {{ request()->routeIs('users.*') ? 'font-bold' : '' }}">
                    Pengguna
                </a>
                <a href="{{ route('about') }}"
                   class="hover:text-slate-300 {{ request()->routeIs('about') ? 'font-bold' : '' }}">
                    Tentang
                </a>
            </div>
        </div>
    </nav>

    {{-- Flash message global — berlaku untuk seluruh halaman --}}
    @if (session('success'))
        <div class="max-w-5xl mx-auto px-4 pt-4">
            <div class="flex items-start gap-3 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                <svg class="mt-0.5 h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="max-w-5xl mx-auto px-4 pt-4">
            <div class="flex items-start gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <svg class="mt-0.5 h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-1-9a1 1 0 012 0v4a1 1 0 01-2 0V9zm1-4a1 1 0 110 2 1 1 0 010-2z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <main class="max-w-5xl mx-auto px-4 py-8">
        {{ $slot }}
    </main>

</body>
</html>

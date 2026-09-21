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
                   class="hover:text-slate-300 {{ request()->routeIs('dashboard') ? 'font-bold underline' : '' }}">
                    Dashboard
                </a>
                <a href="{{ route('courses.index') }}"
                   class="hover:text-slate-300 {{ request()->routeIs('courses.*') ? 'font-bold underline' : '' }}">
                    Mata Kuliah
                </a>
                <a href="{{ route('users.index') }}"
                   class="hover:text-slate-300 {{ request()->routeIs('users.*') ? 'font-bold underline' : '' }}">
                    Pengguna
                </a>
                <a href="{{ route('about') }}"
                   class="hover:text-slate-300 {{ request()->routeIs('about') ? 'font-bold underline' : '' }}">
                    Tentang
                </a>
            </div>
        </div>
    </nav>

    <main class="max-w-5xl mx-auto px-4 py-8">
        {{-- Flash message sukses/gagal di layout agar berlaku untuk seluruh halaman --}}
        @if (session('success'))
            <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 rounded shadow-sm"
                 style="background-color: #ecfdf5; border-left: 4px solid #10b981; color: #065f46; padding: 12px 16px; margin-bottom: 1.5rem; border-radius: 6px;">
                <span style="font-weight: 600;">✓ Berhasil:</span> {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 p-4 bg-rose-50 border-l-4 border-rose-500 text-rose-800 rounded shadow-sm"
                 style="background-color: #fff1f2; border-left: 4px solid #f43f5e; color: #9f1239; padding: 12px 16px; margin-bottom: 1.5rem; border-radius: 6px;">
                <span style="font-weight: 600;">✕ Terjadi Kesalahan:</span> {{ session('error') }}
            </div>
        @endif

        {{ $slot }}
    </main>

</body>
</html>
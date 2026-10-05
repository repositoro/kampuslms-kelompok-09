<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Kampus LMS' }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet" />

    <!-- Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="bg-[#F8FAFC] text-slate-800 antialiased min-h-screen flex flex-col">

    <!-- Navbar -->
    <nav class="bg-[#0F172A] text-white shadow-sm border-b border-slate-800 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between py-3 h-16">
                <!-- Anak 1 (Sisi Kiri): Brand & Menu -->
                <div class="flex items-center space-x-8">
                    <!-- Brand -->
                    <a href="{{ url('/') }}" class="text-xl font-extrabold tracking-tight text-white hover:text-blue-200 transition-colors">Kampus LMS</a>
                    
                    <!-- Desktop Menu -->
                    <div class="hidden md:flex items-center space-x-2">
                        @if(request()->routeIs('welcome') || request()->routeIs('about'))
                            <a href="{{ route('welcome') }}" class="text-sm font-semibold transition-all {{ request()->routeIs('welcome') ? 'bg-blue-500/10 text-blue-400 px-3 py-2 rounded-lg' : 'text-slate-300 hover:text-white hover:bg-slate-800/50 px-3 py-2 rounded-lg' }}">
                                Beranda
                            </a>
                            <a href="{{ route('about') }}" class="text-sm font-semibold transition-all {{ request()->routeIs('about') ? 'bg-blue-500/10 text-blue-400 px-3 py-2 rounded-lg' : 'text-slate-300 hover:text-white hover:bg-slate-800/50 px-3 py-2 rounded-lg' }}">
                                Tentang
                            </a>
                        @else
                            <a href="{{ route('dashboard') }}" class="text-sm font-semibold transition-all {{ request()->routeIs('dashboard') ? 'bg-blue-500/10 text-blue-400 px-3 py-2 rounded-lg' : 'text-slate-300 hover:text-white hover:bg-slate-800/50 px-3 py-2 rounded-lg' }}">
                                Dashboard
                            </a>
                            @auth
                                @if(auth()->user()->role == 'admin')
                                <a href="{{ route('admin.courses.index') }}" class="text-sm font-semibold transition-all {{ request()->routeIs('admin.courses.*') ? 'bg-blue-500/10 text-blue-400 px-3 py-2 rounded-lg' : 'text-slate-300 hover:text-white hover:bg-slate-800/50 px-3 py-2 rounded-lg' }}">
                                    Mata Kuliah
                                </a>
                                <a href="{{ route('admin.users.index') }}" class="text-sm font-semibold transition-all {{ request()->routeIs('admin.users.*') ? 'bg-blue-500/10 text-blue-400 px-3 py-2 rounded-lg' : 'text-slate-300 hover:text-white hover:bg-slate-800/50 px-3 py-2 rounded-lg' }}">
                                    Pengguna
                                </a>
                                @endif
                            @endauth
                        @endif
                    </div>
                </div>

                <!-- Anak 2 (Sisi Kanan): Auth / Profile -->
                <div class="hidden md:flex items-center justify-end space-x-3">
                    @if(request()->routeIs('welcome') || request()->routeIs('about'))
                        <a href="{{ route('login') }}" class="bg-blue-600 hover:bg-blue-500 text-white font-medium px-4 py-2 rounded-xl text-sm transition-all shadow-sm">Log in</a>
                    @else
                        @auth
                            <div class="flex items-center space-x-3 bg-slate-800/80 rounded-full py-1.5 px-2 border border-slate-700 hover:border-slate-600 transition-colors">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center text-xs font-bold text-white shadow-inner">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                                <span class="text-sm font-medium text-slate-200 truncate max-w-[120px]">{{ auth()->user()->name }}</span>
                                <a href="{{ url('/logout-cepat') }}" class="ml-2 p-1.5 rounded-full text-slate-400 hover:text-red-400 hover:bg-slate-700 transition-colors" title="Logout">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                </a>
                            </div>
                        @else
                            <a href="{{ route('login') }}" class="bg-blue-600 hover:bg-blue-500 text-white font-medium px-4 py-2 rounded-xl text-sm transition-all shadow-sm">Log in</a>
                        @endauth
                    @endif
                </div>

                <!-- Mobile menu button -->
                <div class="-mr-2 flex items-center md:hidden">
                    <button type="button" onclick="document.getElementById('mobile-menu').classList.toggle('hidden')" class="inline-flex items-center justify-center p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800 focus:outline-none focus:bg-slate-800 focus:text-white transition-colors">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div class="hidden md:hidden border-t border-slate-700 bg-[#0F172A]" id="mobile-menu">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                @if(request()->routeIs('welcome') || request()->routeIs('about'))
                    <a href="{{ route('welcome') }}" class="block px-3 py-2 rounded-xl text-base font-medium transition-all {{ request()->routeIs('welcome') ? 'bg-blue-500/10 text-blue-400' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">Beranda</a>
                    <a href="{{ route('about') }}" class="block px-3 py-2 rounded-xl text-base font-medium transition-all {{ request()->routeIs('about') ? 'bg-blue-500/10 text-blue-400' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">Tentang</a>
                @else
                    <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded-xl text-base font-medium transition-all {{ request()->routeIs('dashboard') ? 'bg-blue-500/10 text-blue-400' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">Dashboard</a>
                    @auth
                        @if(auth()->user()->role == 'admin')
                        <a href="{{ route('admin.courses.index') }}" class="block px-3 py-2 rounded-xl text-base font-medium transition-all {{ request()->routeIs('admin.courses.*') ? 'bg-blue-500/10 text-blue-400' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">Mata Kuliah</a>
                        <a href="{{ route('admin.users.index') }}" class="block px-3 py-2 rounded-xl text-base font-medium transition-all {{ request()->routeIs('admin.users.*') ? 'bg-blue-500/10 text-blue-400' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">Pengguna</a>
                        @endif
                    @endauth
                @endif
            </div>
            
            @if(request()->routeIs('welcome') || request()->routeIs('about'))
                <div class="pt-4 pb-3 border-t border-slate-700">
                    <div class="mt-3 px-4 space-y-1">
                        <a href="{{ route('login') }}" class="block w-full text-center px-4 py-3 rounded-xl text-sm font-bold text-white bg-blue-600 hover:bg-blue-500 transition-colors shadow-sm">Log in</a>
                    </div>
                </div>
            @else
                @auth
                <div class="pt-4 pb-3 border-t border-slate-700">
                    <div class="flex items-center px-5">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center font-bold text-white shadow-inner">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                        </div>
                        <div class="ml-3">
                            <div class="text-base font-medium leading-none text-white">{{ auth()->user()->name }}</div>
                            <div class="text-sm font-medium leading-none text-slate-400 mt-1">{{ auth()->user()->email }}</div>
                        </div>
                    </div>
                    <div class="mt-3 px-4 space-y-1">
                        <a href="{{ url('/logout-cepat') }}" class="block w-full text-center px-4 py-2.5 rounded-xl text-sm font-medium text-white bg-slate-700 hover:bg-red-500 transition-colors shadow-sm mt-4">Sign out</a>
                    </div>
                </div>
                @endauth
            @endif
        </div>
    </nav>

    <!-- Page Content -->
    <main class="flex-grow w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Flash Messages -->
        @if (session('success'))
            <div class="mb-6 bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-r-2xl shadow-sm flex items-start">
                <svg class="w-5 h-5 text-emerald-500 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <div>
                    <h3 class="text-sm font-bold text-emerald-800">Berhasil</h3>
                    <p class="text-sm text-emerald-700 mt-1">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-2xl shadow-sm flex items-start">
                <svg class="w-5 h-5 text-red-500 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <div>
                    <h3 class="text-sm font-bold text-red-800">Gagal</h3>
                    <p class="text-sm text-red-700 mt-1">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex flex-col md:flex-row items-center justify-between">
            <p class="text-sm text-slate-500 font-medium">&copy; {{ date('Y') }} Kampus LMS — Kelompok 9.</p>
            <p class="text-sm text-slate-400 mt-2 md:mt-0">Repositoro</p>
        </div>
    </footer>

</body>
</html>

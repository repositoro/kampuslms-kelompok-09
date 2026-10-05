<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Log in — Kampus LMS</title>
    
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet" />

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex items-center justify-center relative overflow-hidden">

    <!-- Decorative Background Shapes -->
    <div class="absolute -top-32 -left-32 w-96 h-96 bg-blue-100 rounded-full blur-3xl opacity-50"></div>
    <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-indigo-100 rounded-full blur-3xl opacity-50"></div>

    <div class="w-full max-w-[26rem] px-4 relative z-10">
        
        <!-- Logo / Brand -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-500 shadow-lg text-white mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"></path></svg>
            </div>
            <h1 class="text-3xl font-extrabold tracking-tight text-slate-800">Masuk ke LMS</h1>
            <p class="text-slate-500 mt-2 text-sm font-medium">Gunakan kredensial akademik Anda</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-slate-100/60 backdrop-blur-sm">
            <form action="{{ url('/login-sebagai/1') }}" method="GET" class="space-y-5">
                
                {{-- Email --}}
                <div class="space-y-1.5">
                    <label for="email" class="block text-sm font-bold text-slate-700">Email Kampus</label>
                    <input 
                        type="email" id="email" name="email" required
                        placeholder="mahasiswa@kampus.ac.id"
                        class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:bg-white transition-all font-medium text-slate-700"
                    >
                </div>

                {{-- Password --}}
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label for="password" class="block text-sm font-bold text-slate-700">Kata Sandi</label>
                        <a href="#" class="text-xs font-bold text-blue-600 hover:text-blue-700 transition-colors">Lupa sandi?</a>
                    </div>
                    <input 
                        type="password" id="password" name="password" required
                        placeholder="••••••••"
                        class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:bg-white transition-all font-medium text-slate-700"
                    >
                </div>

                {{-- Dummy Checkbox --}}
                <div class="flex items-center pt-1">
                    <input type="checkbox" id="remember" class="w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500 transition-colors">
                    <label for="remember" class="ml-2.5 text-sm font-medium text-slate-600 cursor-pointer">Ingat sesi saya</label>
                </div>

                {{-- Submit (Directly redirects via GET shortcut /login-sebagai/1 for dummy login) --}}
                <div class="pt-2">
                    <button type="submit" class="w-full py-3.5 rounded-xl bg-blue-600 text-white font-bold text-sm shadow-sm hover:bg-blue-500 hover:shadow transition-all flex items-center justify-center group">
                        Masuk
                        <svg class="w-4 h-4 ml-1.5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </button>
                </div>
            </form>
            
            <div class="mt-8 text-center border-t border-slate-100 pt-6">
                <p class="text-sm font-medium text-slate-500">
                    Ada kendala masuk? 
                    <a href="#" class="font-bold text-blue-600 hover:text-blue-700 transition-colors">Hubungi IT</a>
                </p>
            </div>
        </div>

        <!-- Back to Home -->
        <div class="mt-8 text-center">
            <a href="{{ route('welcome') }}" class="inline-flex items-center justify-center gap-1.5 text-sm font-bold text-slate-400 hover:text-slate-600 transition-colors bg-white/50 px-4 py-2 rounded-full border border-slate-200 shadow-sm backdrop-blur-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Kembali ke Beranda
            </a>
        </div>
        
    </div>

</body>
</html>

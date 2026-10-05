<x-layout title="Dashboard">

    <div class="space-y-8">

        <!-- Hero Banner -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-blue-800 to-blue-600 p-8 md:p-12 shadow-md">
            <div class="relative z-10 text-white">
                <h1 class="text-3xl md:text-5xl font-extrabold tracking-tight mb-3">Selamat Datang di KampusLMS</h1>
                <p class="text-blue-100 text-lg max-w-xl leading-relaxed">Platform pembelajaran terpadu masa depan. Mari mulai eksplorasi mata kuliah dan materi Anda hari ini.</p>
            </div>
            <!-- Decorative Elements -->
            <div class="absolute -right-16 -top-16 w-56 h-56 bg-white opacity-10 rounded-full blur-3xl"></div>
            <div class="absolute right-24 -bottom-16 w-40 h-40 bg-blue-600 opacity-20 rounded-full blur-3xl"></div>
            <div class="absolute left-1/2 -bottom-8 w-32 h-32 bg-blue-300 opacity-15 rounded-full blur-2xl"></div>
        </div>

        <!-- Bento Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <!-- Stat Card: Mata Kuliah Aktif -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 hover:shadow-md transition-all duration-300">
                <div class="flex items-center space-x-4">
                    <div class="w-14 h-14 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                    </div>
                    <div>
                        <p class="text-slate-500 text-sm font-medium">Mata Kuliah Aktif</p>
                        <h3 class="text-3xl font-bold text-slate-800">12</h3>
                    </div>
                </div>
            </div>

            <!-- Stat Card: Tugas Mendatang -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 hover:shadow-md transition-all duration-300">
                <div class="flex items-center space-x-4">
                    <div class="w-14 h-14 rounded-2xl bg-blue-50 text-blue-800 flex items-center justify-center">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    </div>
                    <div>
                        <p class="text-slate-500 text-sm font-medium">Tugas Mendatang</p>
                        <h3 class="text-3xl font-bold text-slate-800">5</h3>
                    </div>
                </div>
            </div>

            <!-- Profile Card -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 hover:shadow-md transition-all duration-300 md:row-span-2 flex flex-col items-center text-center">
                <div class="w-24 h-24 rounded-full bg-gradient-to-br from-blue-500 to-blue-800 mb-4 overflow-hidden border-4 border-blue-50 flex items-center justify-center shadow-md">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name ?? 'User') }}&background=6366f1&color=fff&size=128" alt="Avatar" class="w-full h-full object-cover">
                </div>
                <h3 class="text-xl font-bold text-slate-800">{{ Auth::user()->name ?? 'Mahasiswa / Dosen' }}</h3>
                <p class="text-slate-500 text-sm mb-1">{{ Auth::user()->email ?? 'email@kampus.com' }}</p>
                @auth
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 mb-6">
                        {{ ucfirst(Auth::user()->role ?? 'user') }}
                    </span>
                @endauth

                <div class="w-full mt-auto space-y-3">
                    @auth
                        @if(auth()->user()->role === 'admin')
                            <a href="{{ route('admin.courses.index') }}" class="block w-full py-3 rounded-2xl bg-blue-50 text-blue-700 font-semibold hover:bg-blue-100 transition-colors text-sm">
                                Lihat Mata Kuliah
                            </a>
                        @elseif(auth()->user()->role === 'dosen')
                            <a href="{{ route('dosen.courses.index') }}" class="block w-full py-3 rounded-2xl bg-blue-50 text-blue-700 font-semibold hover:bg-blue-100 transition-colors text-sm">
                                Lihat Mata Kuliah
                            </a>
                        @elseif(auth()->user()->role === 'mahasiswa')
                            <a href="{{ route('mahasiswa.courses.index') }}" class="block w-full py-3 rounded-2xl bg-blue-50 text-blue-700 font-semibold hover:bg-blue-100 transition-colors text-sm">
                                Lihat Mata Kuliah
                            </a>
                        @endif
                    @endauth
                    <form method="POST" action="/logout" class="block w-full">
                        @csrf
                        <button type="submit" class="w-full py-3 rounded-2xl bg-slate-50 text-slate-600 font-semibold hover:bg-slate-100 transition-colors text-sm">
                            Keluar
                        </button>
                    </form>
                </div>
            </div>

            <!-- Pengumuman Card -->
            <div class="bg-white rounded-2xl p-8 shadow-sm border border-slate-100 hover:shadow-md transition-all duration-300 md:col-span-2">
                <h2 class="text-2xl font-bold text-slate-800 mb-5">Pengumuman Terbaru</h2>
                <div class="space-y-4">
                    <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100 hover:border-blue-100 transition-colors">
                        <div class="flex items-start justify-between">
                            <div>
                                <h4 class="font-semibold text-slate-800">Jadwal Ujian Tengah Semester</h4>
                                <p class="text-sm text-slate-500 mt-1">Ujian akan dilaksanakan mulai tanggal 15 bulan depan. Persiapkan diri Anda.</p>
                            </div>
                            <span class="shrink-0 ml-4 px-3 py-1 bg-amber-50 text-amber-700 text-xs font-semibold rounded-full">Penting</span>
                        </div>
                    </div>
                    <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100 hover:border-blue-100 transition-colors">
                        <div class="flex items-start justify-between">
                            <div>
                                <h4 class="font-semibold text-slate-800">Pemeliharaan Sistem LMS</h4>
                                <p class="text-sm text-slate-500 mt-1">Sistem akan offline sementara pada hari Minggu pukul 02:00 - 04:00 pagi.</p>
                            </div>
                            <span class="shrink-0 ml-4 px-3 py-1 bg-blue-50 text-blue-700 text-xs font-semibold rounded-full">Info</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

</x-layout>
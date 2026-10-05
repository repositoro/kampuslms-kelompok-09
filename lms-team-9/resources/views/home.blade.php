<x-components.layout>
    <div class="min-h-screen bg-slate-50 p-6 md:p-10 font-sans text-slate-800">
        <div class="max-w-7xl mx-auto space-y-8">
            
            <!-- Header Section (Banner) -->
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-600 via-indigo-700 to-purple-700 p-8 md:p-12 shadow-sm border border-slate-100 flex items-center justify-between">
                <div class="relative z-10 text-white">
                    <h1 class="text-3xl md:text-5xl font-extrabold tracking-tight mb-2">Selamat Datang di KampusLMS</h1>
                    <p class="text-indigo-100 text-lg max-w-xl">Platform pembelajaran terpadu masa depan. Mari mulai eksplorasi mata kuliah dan materi Anda hari ini.</p>
                </div>
                <!-- Decorative Circles -->
                <div class="absolute -right-20 -top-20 w-64 h-64 bg-white opacity-10 rounded-full blur-2xl"></div>
                <div class="absolute right-20 -bottom-20 w-48 h-48 bg-purple-400 opacity-20 rounded-full blur-2xl"></div>
            </div>

            <!-- Bento Grid Container -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <!-- Quick Stats 1 -->
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 hover:shadow-md transition-shadow flex flex-col justify-center">
                    <div class="flex items-center space-x-4">
                        <div class="w-14 h-14 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        </div>
                        <div>
                            <p class="text-slate-500 text-sm font-medium">Mata Kuliah Aktif</p>
                            <h3 class="text-3xl font-bold text-slate-800">12</h3>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats 2 -->
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 hover:shadow-md transition-shadow flex flex-col justify-center">
                    <div class="flex items-center space-x-4">
                        <div class="w-14 h-14 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                        </div>
                        <div>
                            <p class="text-slate-500 text-sm font-medium">Tugas Mendatang</p>
                            <h3 class="text-3xl font-bold text-slate-800">5</h3>
                        </div>
                    </div>
                </div>

                <!-- Profile / Status -->
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 hover:shadow-md transition-shadow md:col-span-1 row-span-2 flex flex-col items-center text-center">
                    <div class="w-24 h-24 rounded-full bg-slate-200 mb-4 overflow-hidden border-4 border-indigo-50">
                        <!-- Placeholder Avatar -->
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name ?? 'User') }}&background=6366f1&color=fff" alt="Avatar" class="w-full h-full object-cover">
                    </div>
                    <h3 class="text-xl font-bold text-slate-800">{{ Auth::user()->name ?? 'Mahasiswa / Dosen' }}</h3>
                    <p class="text-slate-500 text-sm mb-6">{{ Auth::user()->email ?? 'email@kampus.com' }}</p>
                    
                    <div class="w-full mt-auto space-y-3">
                        <a href="{{ route('courses.index') ?? '#' }}" class="block w-full py-3 rounded-2xl bg-indigo-50 text-indigo-700 font-semibold hover:bg-indigo-100 transition-colors">
                            Lihat Mata Kuliah
                        </a>
                        <form method="POST" action="/logout" class="block w-full">
                            @csrf
                            <button type="submit" class="w-full py-3 rounded-2xl bg-slate-50 text-slate-600 font-semibold hover:bg-slate-100 transition-colors">
                                Keluar
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Main Feature Box -->
                <div class="bg-white rounded-3xl p-8 shadow-sm border border-slate-100 hover:shadow-md transition-shadow md:col-span-2">
                    <h2 class="text-2xl font-bold text-slate-800 mb-4">Pengumuman Terbaru</h2>
                    <div class="space-y-4">
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                            <h4 class="font-semibold text-slate-800">Jadwal Ujian Tengah Semester</h4>
                            <p class="text-sm text-slate-500 mt-1">Ujian akan dilaksanakan mulai tanggal 15 bulan depan. Persiapkan diri Anda.</p>
                        </div>
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                            <h4 class="font-semibold text-slate-800">Pemeliharaan Sistem LMS</h4>
                            <p class="text-sm text-slate-500 mt-1">Sistem akan offline sementara pada hari Minggu pukul 02:00 - 04:00 pagi.</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-components.layout>
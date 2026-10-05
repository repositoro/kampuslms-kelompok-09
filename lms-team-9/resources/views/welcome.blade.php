<x-layout title="Selamat Datang di Kampus LMS">
    
    {{-- Hero Section --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-blue-800 via-blue-600 to-blue-700 rounded-3xl mt-2 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 md:py-32 relative z-10 text-center">
            <h1 class="text-4xl md:text-6xl font-extrabold text-white tracking-tight mb-6 leading-tight">
                Selamat Datang di <br><span class="text-blue-100">Kampus LMS</span>
            </h1>
            <p class="text-lg md:text-xl text-blue-100 mb-10 leading-relaxed max-w-2xl mx-auto">
                Platform Learning Management System modern yang dirancang khusus untuk kemudahan civitas akademika dalam proses belajar-mengajar.
            </p>
            <div class="flex flex-wrap gap-4 justify-center">
                @auth
                    <a href="{{ route('dashboard') }}"
                       class="inline-flex items-center px-8 py-4 rounded-2xl bg-white text-blue-800 font-bold text-lg shadow-lg hover:shadow-xl hover:bg-blue-50 transition-all">
                        Masuk ke Dashboard
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                    </a>
                @else
                    <a href="{{ route('login') }}"
                       class="inline-flex items-center px-8 py-4 rounded-2xl bg-white text-blue-800 font-bold text-lg shadow-lg hover:shadow-xl hover:bg-blue-50 transition-all">
                        Masuk ke LMS
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                    </a>
                @endauth
            </div>
        </div>
        <!-- Decorative -->
        <div class="absolute -right-20 top-10 w-96 h-96 bg-white opacity-5 rounded-full blur-3xl"></div>
        <div class="absolute left-1/4 -bottom-20 w-72 h-72 bg-blue-400 opacity-10 rounded-full blur-3xl"></div>
    </section>

    {{-- Features Section --}}
    <section class="py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-14">
                <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight mb-3">Solusi Belajar Terpadu</h2>
                <p class="text-slate-500 text-lg max-w-2xl mx-auto">Nikmati fitur yang memudahkan kegiatan akademik sehari-hari.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                {{-- Feature 1 --}}
                <div class="bg-white rounded-2xl p-8 shadow-sm border border-slate-100 hover:shadow-md transition-all duration-300">
                    <div class="w-14 h-14 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mb-5">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-800 mb-2">Materi Kuliah</h3>
                    <p class="text-slate-500 leading-relaxed">Akses modul, slide presentasi, dan referensi akademik kapan saja dan di mana saja.</p>
                </div>

                {{-- Feature 2 --}}
                <div class="bg-white rounded-2xl p-8 shadow-sm border border-slate-100 hover:shadow-md transition-all duration-300">
                    <div class="w-14 h-14 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center mb-5">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-800 mb-2">Tugas & Ujian</h3>
                    <p class="text-slate-500 leading-relaxed">Pengumpulan tugas digital dan penilaian yang efisien tanpa kertas.</p>
                </div>
                
                {{-- Feature 3 --}}
                <div class="bg-white rounded-2xl p-8 shadow-sm border border-slate-100 hover:shadow-md transition-all duration-300">
                    <div class="w-14 h-14 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center mb-5">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-800 mb-2">Kolaborasi Aktif</h3>
                    <p class="text-slate-500 leading-relaxed">Sistem pembagian peran yang rapi antara Admin, Dosen, dan Mahasiswa.</p>
                </div>
            </div>
        </div>
    </section>

</x-layout>

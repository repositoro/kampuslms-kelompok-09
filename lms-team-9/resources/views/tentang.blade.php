<x-layout title="Tentang">

    <div class="space-y-8">

        <!-- Hero Banner -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-blue-800 to-blue-600 p-8 md:p-12 shadow-md">
            <div class="relative z-10 text-white">
                <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight mb-3">Tentang Kampus LMS</h1>
                <p class="text-blue-100 text-lg max-w-xl leading-relaxed">Dikembangkan sebagai proyek mata kuliah Pemrograman Web oleh Kelompok 9.</p>
            </div>
            <div class="absolute -right-16 -top-16 w-56 h-56 bg-white opacity-10 rounded-full blur-3xl"></div>
            <div class="absolute right-24 -bottom-16 w-40 h-40 bg-blue-600 opacity-20 rounded-full blur-3xl"></div>
        </div>

        <!-- Anggota Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

            @php
                $anggota = [
                    ['nama' => 'Yiesan Daffa Azrikfi', 'nim' => '10241075', 'warna' => 'from-blue-500 to-blue-600'],
                    ['nama' => 'Zaskiya Salwa Nafitri', 'nim' => '10241077', 'warna' => 'from-blue-700 to-pink-600'],
                    ['nama' => 'Vera Friska', 'nim' => '10241073', 'warna' => 'from-violet-500 to-blue-800'],
                    ['nama' => 'Toro Ababil Tamami Darojat (Captain)', 'nim' => '10241071', 'warna' => 'from-blue-600 to-violet-600'],
                ];
            @endphp

            @foreach($anggota as $member)
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 hover:shadow-md transition-all duration-300 flex flex-col items-center text-center group">
                    <div class="w-20 h-20 rounded-full bg-gradient-to-br {{ $member['warna'] }} flex items-center justify-center text-2xl font-bold text-white mb-4 shadow-md group-hover:scale-105 transition-transform duration-300">
                        {{ strtoupper(substr($member['nama'], 0, 1)) }}
                    </div>
                    <h3 class="text-base font-bold text-slate-800 mb-1">{{ $member['nama'] }}</h3>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700">
                        {{ $member['nim'] }}
                    </span>
                </div>
            @endforeach

        </div>

        <!-- Info Card -->
        <div class="bg-white rounded-2xl p-8 shadow-sm border border-slate-100">
            <h2 class="text-xl font-bold text-slate-800 mb-3">Tentang Proyek</h2>
            <p class="text-slate-600 leading-relaxed">
                KampusLMS merupakan aplikasi Learning Management System (LMS) berbasis web yang dikembangkan sebagai tugas besar mata kuliah Pemrograman Web. Aplikasi ini dirancang untuk mendukung proses pembelajaran di lingkungan perguruan tinggi melalui pengelolaan pengguna, mata kuliah, materi, tugas, pengumpulan tugas, dan penilaian.
                Proyek ini dikembangkan menggunakan <span class="font-semibold text-blue-600">Laravel 12</span> dengan antarmuka modern berbasis <span class="font-semibold text-blue-600">Tailwind CSS</span>.
            </p>
        </div>

    </div>

</x-layout>

<x-layout title="Dashboard Dosen">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Dashboard Dosen</h1>
        <p class="text-sm text-gray-600">Selamat datang, {{ auth()->user()->name }}!</p>
    </div>

    {{-- Ringkasan --}}
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 mb-8">
        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <span class="text-xs font-semibold uppercase tracking-wider text-gray-400">Mata Kuliah Diampu</span>
            <p class="mt-2 text-3xl font-bold text-slate-800">{{ $courses->count() }}</p>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <span class="text-xs font-semibold uppercase tracking-wider text-gray-400">Pengumpulan Belum Dinilai</span>
            <p class="mt-2 text-3xl font-bold text-amber-600">{{ $pendingGradesCount }}</p>
        </div>
    </div>

    {{-- Daftar Kelas Diampu --}}
    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900">Mata Kuliah Anda</h2>
            <a href="{{ route('courses.index') }}" class="text-sm text-slate-600 hover:underline">Lihat Semua →</a>
        </div>

        <div class="divide-y divide-gray-100">
            @forelse ($courses as $course)
                <div class="py-3 flex items-center justify-between">
                    <div>
                        <p class="font-medium text-gray-800">{{ $course->name }} <span class="font-mono text-xs text-gray-500">({{ $course->code }})</span></p>
                        <p class="text-xs text-gray-500">{{ $course->students_count }} Mahasiswa terdaftar • {{ $course->assignments_count }} Tugas</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('dosen.courses.materials.index', $course) }}" 
                           class="text-xs font-medium text-slate-600 hover:underline">
                            Materi
                        </a>
                        <a href="{{ route('dosen.courses.assignments.index', $course) }}" 
                           class="text-xs font-medium text-slate-800 hover:underline">
                            Tugas
                        </a>
                    </div>
                </div>
            @empty
                <p class="py-4 text-center text-sm text-gray-400">Anda belum ditugaskan mengampu mata kuliah apa pun.</p>
            @endforelse
        </div>
    </div>
</x-layout>
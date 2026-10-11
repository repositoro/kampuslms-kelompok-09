<x-layout title="Dashboard Mahasiswa">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Dashboard Mahasiswa</h1>
        <p class="text-sm text-gray-600">Selamat datang, {{ auth()->user()->name }}!</p>
        <a href="{{ route('mahasiswa.grades.index') }}" class="text-sm text-blue-700 underline">Rekap nilai saya</a>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Kolom Kiri: Kelas Saya --}}
        <div class="lg:col-span-2 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Mata Kuliah Saya</h2>
                <a href="{{ route('courses.index') }}" class="text-sm text-slate-600 hover:underline">Lihat Semua →</a>
            </div>

            <div class="divide-y divide-gray-100">
                @forelse ($courses as $course)
                    <div class="py-3 flex items-center justify-between">
                        <div>
                            <a href="{{ route('courses.show', $course) }}" class="font-medium text-slate-800 hover:underline">
                                {{ $course->name }}
                            </a>
                            <p class="text-xs text-gray-500">Dosen: {{ $course->lecturer?->name ?? '—' }}</p>
                        </div>
                        <a href="{{ route('courses.show', $course) }}" class="text-xs font-medium text-slate-700 hover:underline">
                            Buka Kelas →
                        </a>
                    </div>
                @empty
                    <p class="py-4 text-center text-sm text-gray-400">Anda belum terdaftar di mata kuliah apa pun.</p>
                @endforelse
            </div>
        </div>

        {{-- Kolom Kanan: Tugas Mendekati Deadline --}}
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-semibold text-gray-900">Tugas Mendekati Tenggat</h2>

            <div class="space-y-3">
                @forelse ($upcomingAssignments as $assignment)
                    <div class="rounded-md border border-amber-100 bg-amber-50/50 p-3">
                        <p class="font-medium text-sm text-gray-900">{{ $assignment->title }}</p>
                        <p class="text-xs text-gray-500">{{ $assignment->course->name }}</p>
                        <p class="mt-1 text-xs font-semibold text-amber-700">
                            Tenggat: {{ \Carbon\Carbon::parse($assignment->due_at)->format('d M Y, H:i') }}
                        </p>
                        @php($submission = $assignment->submissions->first())
                        <p class="mt-1 text-xs text-gray-700">
                            Status:
                            {{ !$submission ? 'Belum mengumpulkan' : ($submission->is_late ? 'Terkumpul terlambat' : 'Terkumpul tepat waktu') }}
                        </p>
                        <a href="{{ route('dosen.assignments.show', $assignment) }}" class="text-xs text-blue-700 underline">
                            Lihat tugas
                        </a>
                    </div>
                @empty
                    <p class="text-center text-sm text-gray-400 py-4">Tidak ada tugas aktif mendatang.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-layout>
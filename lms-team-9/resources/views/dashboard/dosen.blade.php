<x-layout title="Dashboard Dosen">
    <x-page-header title="Dashboard Dosen" subtitle="Selamat datang, {{ auth()->user()->name }}." />
    <div class="stats-grid">
        <x-card>
            <p class="stat-label">Mata kuliah diampu</p>
            <p class="stat-value">{{ $courses->total() }}</p>
        </x-card>
        <x-card>
            <p class="stat-label">Pengumpulan belum dinilai</p>
            <p class="stat-value">{{ $pendingGradesCount }}</p>
        </x-card>
    </div>
    <x-card title="Mata kuliah saya">
        @forelse ($courses as $course)
            <div class="list-row">
                <div>
                    <span class="course-code">{{ $course->code }}</span>
                    <h3>{{ $course->name }}</h3>
                    <p class="muted">{{ $course->students_count }} mahasiswa · {{ $course->assignments_count }} tugas</p>
                </div>
                <div class="cluster">
                    <a href="{{ route('dosen.courses.materials.index', $course) }}">Materi</a>
                    <a href="{{ route('dosen.courses.assignments.index', $course) }}">Tugas</a>
                </div>
            </div>
        @empty
            <x-empty-state title="Belum ada mata kuliah yang diampu" />
        @endforelse
        {{ $courses->links('vendor.pagination.simple') }}
    </x-card>
</x-layout>

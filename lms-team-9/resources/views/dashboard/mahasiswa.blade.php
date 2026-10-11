<x-layout title="Dashboard Mahasiswa">
    <x-page-header title="Dashboard Mahasiswa" subtitle="Selamat datang, {{ auth()->user()->name }}.">
        <x-slot:actions>
            <a class="button button--secondary" href="{{ route('mahasiswa.grades.index') }}">Nilai saya</a>
        </x-slot:actions>
    </x-page-header>
    <div class="dashboard-grid">
        <x-card title="Mata kuliah saya">
            @forelse ($courses as $course)
                <div class="list-row">
                    <div>
                        <span class="course-code">{{ $course->code }}</span>
                        <h3>{{ $course->name }}</h3>
                        <p class="muted">Dosen: {{ $course->lecturer?->name ?? '—' }}</p>
                    </div>
                    <a class="button button--secondary button--small" href="{{ route('courses.show', $course) }}">Buka kelas</a>
                </div>
            @empty
                <x-empty-state title="Belum terdaftar di mata kuliah" />
            @endforelse
        </x-card>
        <x-card title="Tugas mendekati tenggat">
            @forelse ($upcomingAssignments as $assignment)
                @php($remaining = now()->diff($assignment->due_at))
                @php($submission = $assignment->submissions->first())
                <article class="deadline-card">
                    <p><strong>{{ $assignment->title }}</strong></p>
                    <p>{{ $assignment->course->name }}</p>
                    <p class="muted">Tenggat: {{ $assignment->due_at->format('d M Y, H:i') }}</p>
                    <x-badge :variant="$submission ? ($submission->is_late ? 'danger' : 'success') : 'warning'">
                        {{ !$submission ? 'Belum mengumpulkan' : ($submission->is_late ? 'Terkumpul terlambat' : 'Terkumpul tepat waktu') }}
                    </x-badge>
                    @if (!$submission)
                        <p>Sisa {{ $remaining->days }} hari {{ $remaining->h }} jam</p>
                    @endif
                    <p><a href="{{ route('dosen.assignments.show', $assignment) }}">Lihat tugas</a></p>
                </article>
            @empty
                <x-empty-state title="Tidak ada tugas aktif mendatang" />
            @endforelse
        </x-card>
    </div>
</x-layout>

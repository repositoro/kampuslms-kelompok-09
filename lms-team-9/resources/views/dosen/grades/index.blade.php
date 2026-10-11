<x-layout title="Rekap Nilai">
    <x-breadcrumb><a href="{{ route('courses.show', $course) }}">{{ $course->name }}</a><span>Rekap nilai</span></x-breadcrumb>
    <x-page-header title="Rekap nilai" subtitle="{{ $course->name }} · {{ $course->code }}" />
    <x-tabs :tabs="[
        ['label' => 'Ringkasan', 'route' => 'courses.show', 'parameters' => [$course]],
        ['label' => 'Materi', 'route' => 'dosen.courses.materials.index', 'parameters' => [$course], 'active' => 'dosen.courses.materials.*'],
        ['label' => 'Tugas', 'route' => 'dosen.courses.assignments.index', 'parameters' => [$course], 'active' => 'dosen.courses.assignments.*'],
        ['label' => 'Rekap nilai', 'route' => 'dosen.courses.grades.index', 'parameters' => [$course], 'active' => 'dosen.courses.grades.*'],
    ]" />
    <x-card title="Nilai yang sudah diberikan">
        <x-table>
            <thead><tr><th>Tugas</th><th>Mahasiswa</th><th>Nilai</th><th>Umpan balik</th><th>Tanggal penilaian</th></tr></thead>
            <tbody>
                @forelse ($grades as $grade)
                    <tr>
                        <td data-label="Tugas">{{ $grade->submission->assignment->title }}</td>
                        <td data-label="Mahasiswa">{{ $grade->submission->student->name }}</td>
                        <td data-label="Nilai"><x-badge variant="success">{{ $grade->score }} / {{ $grade->submission->assignment->max_score }}</x-badge></td>
                        <td data-label="Umpan balik">{{ $grade->feedback ?: '—' }}</td>
                        <td data-label="Tanggal penilaian">{{ $grade->graded_at->format('d M Y, H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5"><x-empty-state title="Belum ada nilai di mata kuliah ini" /></td></tr>
                @endforelse
            </tbody>
        </x-table>
    </x-card>
</x-layout>

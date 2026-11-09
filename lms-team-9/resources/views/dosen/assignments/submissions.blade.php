<x-layout title="Pengumpulan Tugas">
    <x-breadcrumb>
        <a href="{{ route('courses.show', $assignment->course) }}">{{ $assignment->course->name }}</a>
        <a href="{{ route('dosen.assignments.show', $assignment) }}">{{ $assignment->title }}</a>
        <span>Pengumpulan</span>
    </x-breadcrumb>

    <x-page-header title="Pengumpulan tugas" subtitle="{{ $assignment->title }} · {{ $assignment->course->name }}" />

    <x-card title="Mahasiswa yang mengumpulkan">
        @if ($assignment->submissions->isEmpty())
            <x-empty-state title="Belum ada pengumpulan" />
        @else
            <x-table>
                <thead><tr><th>Mahasiswa</th><th>Waktu kumpul</th><th>Status</th><th>Nilai</th><th>Unduh</th></tr></thead>
                <tbody>
                    @foreach ($assignment->submissions as $studentSubmission)
                        <tr>
                            <td data-label="Mahasiswa">{{ $studentSubmission->student->name }}</td>
                            <td data-label="Waktu kumpul">{{ $studentSubmission->submitted_at->format('d M Y, H:i') }}</td>
                            <td data-label="Status"><x-badge :variant="$studentSubmission->is_late ? 'danger' : 'success'">{{ $studentSubmission->is_late ? 'Terlambat' : 'Tepat waktu' }}</x-badge></td>
                            <td data-label="Nilai">
                                @if ($studentSubmission->grade)
                                    <x-badge variant="success">Dinilai · {{ $studentSubmission->grade->score }} / {{ $assignment->max_score }}</x-badge>
                                @else
                                    <x-badge variant="warning">Belum dinilai</x-badge>
                                @endif
                            </td>
                            <td data-label="Unduh"><a href="{{ route('submissions.download', $studentSubmission) }}">Unduh berkas</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </x-table>
            {{ $assignment->submissions->links('vendor.pagination.simple') }}
        @endif
        <p><a href="{{ route('dosen.assignments.show', $assignment) }}">Buka detail tugas untuk memberi nilai</a></p>
    </x-card>
</x-layout>

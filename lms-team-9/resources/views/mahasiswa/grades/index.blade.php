<x-layout title="Nilai Saya">
    <x-page-header title="Nilai Saya" subtitle="Nilai dan umpan balik dari mata kuliah yang Anda ikuti." />
    <x-card title="Riwayat penilaian">
        <x-table>
            <thead><tr><th>Mata kuliah</th><th>Tugas</th><th>Nilai</th><th>Umpan balik</th><th>Status</th></tr></thead>
            <tbody>
                @forelse ($grades as $grade)
                    <tr>
                        <td data-label="Mata kuliah">{{ $grade->submission->assignment->course->name }}</td>
                        <td data-label="Tugas">{{ $grade->submission->assignment->title }}</td>
                        <td data-label="Nilai"><x-badge variant="success">{{ $grade->score }} / {{ $grade->submission->assignment->max_score }}</x-badge></td>
                        <td data-label="Umpan balik">{{ $grade->feedback ?: 'Belum ada umpan balik.' }}</td>
                        <td data-label="Status"><x-badge variant="success">Dinilai</x-badge></td>
                    </tr>
                @empty
                    <tr><td colspan="5"><x-empty-state title="Belum ada nilai" description="Nilai akan muncul di sini setelah dosen menilai tugas Anda." /></td></tr>
                @endforelse
            </tbody>
        </x-table>
    </x-card>
</x-layout>

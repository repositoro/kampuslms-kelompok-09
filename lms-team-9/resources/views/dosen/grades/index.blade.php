<x-layout title="Rekap Nilai">
    <h1 class="mb-4 text-xl font-bold">Rekap Nilai: {{ $course->name }}</h1>
    <table class="w-full border-collapse text-left text-sm">
        <thead>
            <tr class="border-b">
                <th class="p-2">Tugas</th>
                <th class="p-2">Mahasiswa</th>
                <th class="p-2">Nilai</th>
                <th class="p-2">Feedback</th>
                <th class="p-2">Tanggal penilaian</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($grades as $grade)
                <tr class="border-b">
                    <td class="p-2">{{ $grade->submission->assignment->title }}</td>
                    <td class="p-2">{{ $grade->submission->student->name }}</td>
                    <td class="p-2">{{ $grade->score }}</td>
                    <td class="p-2">{{ $grade->feedback ?? '—' }}</td>
                    <td class="p-2">{{ $grade->graded_at->format('d M Y, H:i') }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="p-2 text-gray-500">Belum ada nilai.</td></tr>
            @endforelse
        </tbody>
    </table>
</x-layout>

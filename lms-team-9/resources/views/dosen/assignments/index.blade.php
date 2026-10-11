<x-layout title="Tugas Mata Kuliah">
    <h1 class="mb-4 text-xl font-bold">Tugas: {{ $course->name }}</h1>
    @if (auth()->user()->role !== 'mahasiswa')
        <a href="{{ route('dosen.courses.assignments.create', $course) }}">Tambah tugas</a>
    @endif
    <ul class="mt-4 space-y-2">
        @forelse ($assignments as $assignment)
            <li>
                <a href="{{ route('dosen.assignments.show', $assignment) }}">{{ $assignment->title }}</a>
                <span>{{ $assignment->status }} — {{ $assignment->due_at }}</span>
                @if (auth()->user()->role === 'mahasiswa')
                    @php($submission = $assignment->submissions->first())
                    <span>
                        {{ !$submission ? 'Belum mengumpulkan' : ($submission->is_late ? 'Terkumpul terlambat' : 'Terkumpul tepat waktu') }}
                    </span>
                @endif
                @if (auth()->user()->role !== 'mahasiswa')
                    <a href="{{ route('dosen.assignments.edit', $assignment) }}">Edit</a>
                @endif
                @can('delete', $assignment)
                    <form
                        action="{{ route('dosen.assignments.destroy', $assignment) }}"
                        method="POST"
                        class="inline"
                        onsubmit="return confirm(this.dataset.confirm)"
                        data-confirm="Hapus tugas ini? {{ $assignment->submissions_count }} pengumpulan dan {{ $assignment->grades_count }} nilai akan ikut terhapus."
                    >
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-700 underline">Hapus</button>
                    </form>
                @endcan
            </li>
        @empty
            <li>Belum ada tugas.</li>
        @endforelse
    </ul>
    {{ $assignments->links() }}
</x-layout>

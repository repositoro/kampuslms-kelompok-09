<x-layout title="Materi Mata Kuliah">
    <h1 class="mb-4 text-xl font-bold">Materi: {{ $course->name }}</h1>
    <a href="{{ route('dosen.courses.materials.create', $course) }}">Tambah materi</a>
    <ul class="mt-4 space-y-2">
        @forelse ($materials as $material)
            <li>
                <a href="{{ route('dosen.materials.show', $material) }}">{{ $material->title }}</a>
                @if ($material->type === 'file')
                    <a href="{{ route('materials.download', $material) }}">Unduh</a>
                @endif
                <a href="{{ route('dosen.materials.edit', $material) }}">Edit</a>
                @can('delete', $material)
                    <form
                        action="{{ route('dosen.materials.destroy', $material) }}"
                        method="POST"
                        class="inline"
                        onsubmit="return confirm('Hapus materi ini?')"
                    >
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-700 underline">Hapus</button>
                    </form>
                @endcan
            </li>
        @empty
            <li>Belum ada materi.</li>
        @endforelse
    </ul>
    {{ $materials->links() }}
</x-layout>

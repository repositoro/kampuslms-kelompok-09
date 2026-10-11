<x-layout title="Detail Materi">
    <h1 class="mb-4 text-xl font-bold">{{ $material->title }}</h1>
    <p>{{ $material->description }}</p>
    @if ($material->type === 'file')
        <a href="{{ route('materials.download', $material) }}">Unduh materi</a>
    @else
        <a href="{{ $material->external_url }}">Buka tautan</a>
    @endif
    @can('delete', $material)
        <form
            action="{{ route('dosen.materials.destroy', $material) }}"
            method="POST"
            class="mt-4"
            onsubmit="return confirm('Hapus materi ini?')"
        >
            @csrf
            @method('DELETE')
            <button type="submit" class="text-red-700 underline">Hapus Materi</button>
        </form>
    @endcan
</x-layout>

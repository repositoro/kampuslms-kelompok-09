<x-layout title="Detail Materi">
    <x-breadcrumb><a href="{{ route('courses.show', $material->course_id) }}">Mata kuliah</a><a href="{{ route('dosen.courses.materials.index', $material->course_id) }}">Materi</a><span>Detail</span></x-breadcrumb>
    <x-page-header title="{{ $material->title }}" subtitle="Materi pembelajaran">
        <x-slot:actions>
            @can('update', $material)<a class="button button--secondary" href="{{ route('dosen.materials.edit', $material) }}">Ubah materi</a>@endcan
        </x-slot:actions>
    </x-page-header>
    <x-card>
        <p class="prose">{{ $material->description ?: 'Tidak ada deskripsi.' }}</p>
        @if ($material->type === 'file')
            <a class="button button--primary" href="{{ route('materials.download', $material) }}">Unduh materi</a>
        @elseif ($material->safeExternalUrl())
            <a class="button button--primary" href="{{ $material->safeExternalUrl() }}" rel="noopener noreferrer">Buka tautan</a>
        @endif
        @can('delete', $material)
            <form action="{{ route('dosen.materials.destroy', $material) }}" method="POST" class="inline-form"
                onsubmit="return confirm('Hapus materi ini?')">
                @csrf
                @method('DELETE')
                <button class="button button--danger" type="submit">Hapus Materi</button>
            </form>
        @endcan
    </x-card>
</x-layout>

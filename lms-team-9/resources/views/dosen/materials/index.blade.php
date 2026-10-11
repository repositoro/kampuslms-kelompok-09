<x-layout title="Materi Mata Kuliah">
    <x-breadcrumb><a href="{{ route('courses.show', $course) }}">{{ $course->name }}</a><span>Materi</span></x-breadcrumb>
    <x-page-header title="Materi" subtitle="Materi pembelajaran untuk {{ $course->name }}.">
        <x-slot:actions>
            @can('create', [App\Models\Material::class, $course])
                <a class="button button--primary" href="{{ route('dosen.courses.materials.create', $course) }}">Tambah materi</a>
            @endcan
        </x-slot:actions>
    </x-page-header>
    <x-tabs :tabs="[
        ['label' => 'Ringkasan', 'route' => 'courses.show', 'parameters' => [$course]],
        ['label' => 'Materi', 'route' => 'dosen.courses.materials.index', 'parameters' => [$course], 'active' => 'dosen.courses.materials.*'],
        ['label' => 'Tugas', 'route' => 'dosen.courses.assignments.index', 'parameters' => [$course], 'active' => 'dosen.courses.assignments.*'],
        ['label' => 'Rekap nilai', 'route' => 'dosen.courses.grades.index', 'parameters' => [$course], 'active' => 'dosen.courses.grades.*'],
    ]" />
    <x-card title="Daftar materi">
        <x-table>
            <thead><tr><th>Materi</th><th>Jenis</th><th>Ditambahkan</th><th>Aksi</th></tr></thead>
            <tbody>
                @forelse ($materials as $material)
                    <tr>
                        <td data-label="Materi"><a href="{{ route('dosen.materials.show', $material) }}"><strong>{{ $material->title }}</strong></a></td>
                        <td data-label="Jenis"><x-badge :variant="$material->type === 'file' ? 'info' : 'neutral'">{{ $material->type === 'file' ? 'Berkas' : 'Tautan' }}</x-badge></td>
                        <td data-label="Ditambahkan">{{ $material->created_at->format('d M Y') }}</td>
                        <td data-label="Aksi">
                            <div class="record-actions">
                                @if ($material->type === 'file')
                                    <a href="{{ route('materials.download', $material) }}">Unduh</a>
                                @elseif ($material->safeExternalUrl())
                                    <a href="{{ $material->safeExternalUrl() }}">Buka tautan</a>
                                @endif
                                @can('update', $material)<a href="{{ route('dosen.materials.edit', $material) }}">Ubah</a>@endcan
                                @can('delete', $material)
                                    <form action="{{ route('dosen.materials.destroy', $material) }}" method="POST" onsubmit="return confirm('Hapus materi ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="button button--danger button--small" type="submit">Hapus</button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4"><x-empty-state title="Belum ada materi" /></td></tr>
                @endforelse
            </tbody>
        </x-table>
        {{ $materials->links('vendor.pagination.simple') }}
    </x-card>
</x-layout>

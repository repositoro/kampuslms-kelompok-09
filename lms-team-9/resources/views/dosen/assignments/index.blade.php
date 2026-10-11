<x-layout title="Tugas Mata Kuliah">
    <x-breadcrumb><a href="{{ route('courses.show', $course) }}">{{ $course->name }}</a><span>Tugas</span></x-breadcrumb>
    <x-page-header title="Tugas" subtitle="Daftar tugas mata kuliah {{ $course->name }}.">
        <x-slot:actions>
            @can('create', [App\Models\Assignment::class, $course])
                <a class="button button--primary" href="{{ route('dosen.courses.assignments.create', $course) }}">Tambah tugas</a>
            @endcan
        </x-slot:actions>
    </x-page-header>
    <x-tabs :tabs="[
        ['label' => 'Ringkasan', 'route' => 'courses.show', 'parameters' => [$course]],
        ['label' => 'Materi', 'route' => 'dosen.courses.materials.index', 'parameters' => [$course], 'active' => 'dosen.courses.materials.*'],
        ['label' => 'Tugas', 'route' => 'dosen.courses.assignments.index', 'parameters' => [$course], 'active' => 'dosen.courses.assignments.*'],
        ['label' => 'Rekap nilai', 'route' => 'dosen.courses.grades.index', 'parameters' => [$course], 'active' => 'dosen.courses.grades.*'],
    ]" />
    <x-card title="Daftar tugas">
        <x-table>
            <thead><tr><th>Judul</th><th>Tenggat</th><th>Status</th><th>Pengumpulan</th><th>Aksi</th></tr></thead>
            <tbody>
                @forelse ($assignments as $assignment)
                    <tr>
                        <td data-label="Judul"><a href="{{ route('dosen.assignments.show', $assignment) }}"><strong>{{ $assignment->title }}</strong></a></td>
                        <td data-label="Tenggat">{{ $assignment->due_at->format('d M Y, H:i') }}</td>
                        <td data-label="Status"><x-badge :variant="$assignment->status === 'published' ? 'success' : 'neutral'">{{ $assignment->status === 'published' ? 'Terbit' : 'Draf' }}</x-badge></td>
                        <td data-label="Pengumpulan">{{ $assignment->submissions_count }}</td>
                        <td data-label="Aksi">
                            <div class="record-actions">
                                <a href="{{ route('dosen.assignments.show', $assignment) }}">Detail</a>
                                @can('update', $assignment)<a href="{{ route('dosen.assignments.edit', $assignment) }}">Ubah</a>@endcan
                                @can('delete', $assignment)
                                    <form action="{{ route('dosen.assignments.destroy', $assignment) }}" method="POST"
                                        onsubmit="return confirm('Hapus tugas ini? {{ $assignment->submissions_count }} pengumpulan dan {{ $assignment->grades_count }} nilai akan ikut terhapus.')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="button button--danger button--small" type="submit">Hapus</button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5"><x-empty-state title="Belum ada tugas" /></td></tr>
                @endforelse
            </tbody>
        </x-table>
        {{ $assignments->links('vendor.pagination.simple') }}
    </x-card>
</x-layout>

<x-layout title="Mata Kuliah">
    <x-page-header title="{{ auth()->user()->role === 'admin' ? 'Mata Kuliah' : 'Mata Kuliah Saya' }}"
        subtitle="Daftar mata kuliah yang tersedia untuk akun Anda.">
        <x-slot:actions>
            @can('create', App\Models\Course::class)
                <a class="button button--primary" href="{{ route('courses.create') }}">Tambah mata kuliah</a>
            @endcan
        </x-slot:actions>
    </x-page-header>
    <div class="course-grid">
        @forelse ($courses as $course)
            <x-card class="course-card">
                <span class="course-code">{{ $course->code }}</span>
                <h2><a href="{{ route('courses.show', $course) }}">{{ $course->name }}</a></h2>
                <p class="muted">{{ $course->description ?: 'Belum ada deskripsi.' }}</p>
                <div class="status-row">
                    <x-badge :variant="$course->status === 'active' ? 'success' : 'neutral'">{{ ['draft' => 'Draf', 'active' => 'Aktif', 'archived' => 'Diarsipkan'][$course->status] ?? $course->status }}</x-badge>
                    <span class="muted">{{ $course->sks }} SKS</span>
                    <span class="muted">Dosen: {{ $course->lecturer?->name ?? '—' }}</span>
                </div>
                <div class="cluster">
                    <a class="button button--secondary button--small" href="{{ route('courses.show', $course) }}">Lihat detail</a>
                    @can('update', $course)
                        <a class="button button--secondary button--small" href="{{ route('courses.edit', $course) }}">Ubah</a>
                    @endcan
                    @can('delete', $course)
                        <form action="{{ route('courses.destroy', $course) }}" method="POST"
                            onsubmit="return confirm('Hapus mata kuliah ini?')">
                            @csrf
                            @method('DELETE')
                            <button class="button button--danger button--small" type="submit">Hapus</button>
                        </form>
                    @endcan
                </div>
            </x-card>
        @empty
            <x-empty-state title="Belum ada mata kuliah" description="Mata kuliah akan muncul di sini saat tersedia." />
        @endforelse
    </div>
    {{ $courses->links('vendor.pagination.simple') }}
</x-layout>

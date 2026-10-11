<x-layout title="Tambah Materi">
    <x-breadcrumb><a href="{{ route('courses.show', $course) }}">{{ $course->name }}</a><a href="{{ route('dosen.courses.materials.index', $course) }}">Materi</a><span>Tambah</span></x-breadcrumb>
    <x-page-header title="Tambah materi" subtitle="Unggah PDF/PPTX atau bagikan tautan pembelajaran." />
    <x-card>
        <form action="{{ route('dosen.courses.materials.store', $course) }}" method="POST" enctype="multipart/form-data" class="form-stack">
            @csrf
            <x-form-field name="title" label="Judul" :required="true">
                <x-input id="title" name="title" value="{{ old('title') }}" required />
            </x-form-field>
            <x-form-field name="description" label="Deskripsi">
                <x-textarea id="description" name="description">{{ old('description') }}</x-textarea>
            </x-form-field>
            <x-form-field name="type" label="Jenis materi" :required="true">
                <x-select id="type" name="type" required>
                    <option value="file" @selected(old('type', 'file') === 'file')>Berkas PDF/PPTX</option>
                    <option value="link" @selected(old('type') === 'link')>Tautan</option>
                </x-select>
            </x-form-field>
            <x-form-field name="file" label="Berkas materi" hint="Format PDF atau PPTX, ukuran maksimal 10 MB.">
                <x-file-input id="file" name="file" accept=".pdf,.pptx" />
            </x-form-field>
            <x-form-field name="external_url" label="URL materi" hint="Masukkan tautan lengkap yang diawali http:// atau https://.">
                <x-input id="external_url" name="external_url" type="url" value="{{ old('external_url') }}" />
            </x-form-field>
            <div class="cluster">
                <x-button>Simpan materi</x-button>
                <a class="button button--secondary" href="{{ route('dosen.courses.materials.index', $course) }}">Batal</a>
            </div>
        </form>
    </x-card>
</x-layout>

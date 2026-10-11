<x-layout title="Edit Materi">
    <x-breadcrumb><a href="{{ route('courses.show', $material->course) }}">{{ $material->course->name }}</a><a href="{{ route('dosen.courses.materials.index', $material->course) }}">Materi</a><span>Ubah</span></x-breadcrumb>
    <x-page-header title="Edit materi" subtitle="{{ $material->title }}" />
    <x-card>
        <form action="{{ route('dosen.materials.update', $material) }}" method="POST" enctype="multipart/form-data" class="form-stack">
            @csrf
            @method('PUT')
            <x-form-field name="title" label="Judul" :required="true">
                <x-input id="title" name="title" value="{{ old('title', $material->title) }}" required />
            </x-form-field>
            <x-form-field name="description" label="Deskripsi">
                <x-textarea id="description" name="description">{{ old('description', $material->description) }}</x-textarea>
            </x-form-field>
            <x-form-field name="type" label="Jenis materi" :required="true">
                <x-select id="type" name="type" required>
                    <option value="file" @selected(old('type', $material->type) === 'file')>Berkas PDF/PPTX</option>
                    <option value="link" @selected(old('type', $material->type) === 'link')>Tautan</option>
                </x-select>
            </x-form-field>
            @if ($material->file_path)
                <p class="muted">Berkas saat ini: {{ $material->original_name }}</p>
            @endif
            <x-form-field name="file" label="Berkas baru" hint="PDF/PPTX, maksimal 10 MB. Biarkan kosong untuk tetap memakai berkas sekarang.">
                <x-file-input id="file" name="file" accept=".pdf,.pptx" />
            </x-form-field>
            <x-form-field name="external_url" label="URL materi" hint="Tautan harus menggunakan http:// atau https://.">
                <x-input id="external_url" name="external_url" type="url" value="{{ old('external_url', $material->external_url) }}" />
            </x-form-field>
            <div class="cluster">
                <x-button>Perbarui materi</x-button>
                <a class="button button--secondary" href="{{ route('dosen.materials.show', $material) }}">Batal</a>
            </div>
        </form>
    </x-card>
</x-layout>

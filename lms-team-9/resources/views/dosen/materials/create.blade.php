<x-layout title="Tambah Materi">
    <x-breadcrumb>
        <a href="{{ route('courses.show', $course) }}">{{ $course->name }}</a>
        <a href="{{ route('dosen.courses.materials.index', $course) }}">Materi</a>
        <span>Tambah</span>
    </x-breadcrumb>

    <x-page-header title="Tambah materi" subtitle="Unggah berkas (PDF/PPTX/DOCX) atau bagikan tautan pembelajaran." />

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
                    <option value="file" @selected(old('type', 'file') === 'file')>Berkas (PDF, PPT, PPTX, DOC, DOCX)</option>
                    <option value="link" @selected(old('type') === 'link')>Tautan Eksternal</option>
                </x-select>
            </x-form-field>

            {{-- Container Input Berkas --}}
            <div id="file-field-container">
                <x-form-field name="file" label="Berkas materi" hint="Format PDF, PPT, PPTX, DOC, atau DOCX, ukuran maksimal 20 MB.">
                    <x-file-input id="file" name="file" accept=".pdf,.pptx,.ppt,.docx,.doc" />
                </x-form-field>
            </div>

            {{-- Container Input URL --}}
            <div id="url-field-container">
                <x-form-field name="external_url" label="URL materi" hint="Masukkan tautan lengkap yang diawali http:// atau https://.">
                    <x-input id="external_url" name="external_url" type="url" value="{{ old('external_url') }}" />
                </x-form-field>
            </div>

            <div class="cluster">
                <x-button>Simpan materi</x-button>
                <a class="button button--secondary" href="{{ route('dosen.courses.materials.index', $course) }}">Batal</a>
            </div>
        </form>
    </x-card>

    {{-- Script toggle tampilan field berdasarkan pilihan select type --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const typeSelect = document.getElementById('type');
            const fileContainer = document.getElementById('file-field-container');
            const urlContainer = document.getElementById('url-field-container');

            function toggleFields() {
                if (typeSelect.value === 'file') {
                    fileContainer.style.display = 'block';
                    urlContainer.style.display = 'none';
                } else {
                    fileContainer.style.display = 'none';
                    urlContainer.style.display = 'block';
                }
            }

            typeSelect.addEventListener('change', toggleFields);
            toggleFields(); // Jalankan saat halaman pertama kali dimuat
        });
    </script>
</x-layout>
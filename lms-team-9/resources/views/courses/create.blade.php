<x-layout title="Tambah Mata Kuliah">
    <x-breadcrumb><a href="{{ route('courses.index') }}">Mata Kuliah</a><span>Tambah mata kuliah</span></x-breadcrumb>
    <x-page-header title="Tambah Mata Kuliah" subtitle="Lengkapi informasi dan dosen pengampu." />
    <x-card>
        <form action="{{ route('courses.store') }}" method="POST" class="form-stack">
            @csrf
            <div class="form-grid">
                <x-form-field name="code" label="Kode mata kuliah" :required="true">
                    <x-input id="code" name="code" value="{{ old('code') }}" required />
                </x-form-field>
                <x-form-field name="name" label="Nama mata kuliah" :required="true">
                    <x-input id="name" name="name" value="{{ old('name') }}" required />
                </x-form-field>
                <x-form-field name="sks" label="SKS" :required="true">
                    <x-input id="sks" name="sks" type="number" min="1" max="255" value="{{ old('sks', 3) }}" required />
                </x-form-field>
                <x-form-field name="lecturer_id" label="Dosen pengampu" :required="true">
                    <x-select id="lecturer_id" name="lecturer_id" required>
                        <option value="">Pilih dosen</option>
                        @foreach ($lecturers as $lecturer)
                            <option value="{{ $lecturer->id }}" @selected(old('lecturer_id') == $lecturer->id)>{{ $lecturer->name }}</option>
                        @endforeach
                    </x-select>
                </x-form-field>
                <x-form-field name="status" label="Status">
                    <x-select id="status" name="status">
                        <option value="draft" @selected(old('status', 'draft') === 'draft')>Draft</option>
                        <option value="active" @selected(old('status') === 'active')>Aktif</option>
                        <option value="archived" @selected(old('status') === 'archived')>Diarsipkan</option>
                    </x-select>
                </x-form-field>
            </div>
            <x-form-field name="description" label="Deskripsi">
                <x-textarea id="description" name="description">{{ old('description') }}</x-textarea>
            </x-form-field>
            <div class="cluster">
                <x-button>Simpan mata kuliah</x-button>
                <a class="button button--secondary" href="{{ route('courses.index') }}">Batal</a>
            </div>
        </form>
    </x-card>
</x-layout>

<x-layout title="Edit Mata Kuliah">
    <x-breadcrumb><a href="{{ route('courses.index') }}">Mata Kuliah</a><a href="{{ route('courses.show', $course) }}">{{ $course->code }}</a><span>Ubah</span></x-breadcrumb>
    <x-page-header title="Edit Mata Kuliah" subtitle="{{ $course->name }}" />
    <x-card>
        <form action="{{ route('courses.update', $course) }}" method="POST" class="form-stack">
            @csrf
            @method('PUT')
            <div class="form-grid">
                <x-form-field name="code" label="Kode mata kuliah" :required="true">
                    <x-input id="code" name="code" value="{{ old('code', $course->code) }}" required />
                </x-form-field>
                <x-form-field name="name" label="Nama mata kuliah" :required="true">
                    <x-input id="name" name="name" value="{{ old('name', $course->name) }}" required />
                </x-form-field>
                <x-form-field name="sks" label="SKS" :required="true">
                    <x-input id="sks" name="sks" type="number" min="1" max="255" value="{{ old('sks', $course->sks) }}" required />
                </x-form-field>
                <x-form-field name="lecturer_id" label="Dosen pengampu" :required="true">
                    <x-select id="lecturer_id" name="lecturer_id" required>
                        @foreach ($lecturers as $lecturer)
                            <option value="{{ $lecturer->id }}" @selected(old('lecturer_id', $course->lecturer_id) == $lecturer->id)>{{ $lecturer->name }}</option>
                        @endforeach
                    </x-select>
                    {{ $lecturers->links('vendor.pagination.simple') }}
                </x-form-field>
                <x-form-field name="status" label="Status" :required="true">
                    <x-select id="status" name="status" required>
                        <option value="draft" @selected(old('status', $course->status) === 'draft')>Draf</option>
                        <option value="active" @selected(old('status', $course->status) === 'active')>Aktif</option>
                        <option value="archived" @selected(old('status', $course->status) === 'archived')>Diarsipkan</option>
                    </x-select>
                </x-form-field>
            </div>
            <x-form-field name="description" label="Deskripsi">
                <x-textarea id="description" name="description">{{ old('description', $course->description) }}</x-textarea>
            </x-form-field>
            <div class="cluster">
                <x-button>Perbarui mata kuliah</x-button>
                <a class="button button--secondary" href="{{ route('courses.show', $course) }}">Batal</a>
            </div>
        </form>
    </x-card>
</x-layout>

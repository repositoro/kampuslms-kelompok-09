<x-layout title="Tambah Tugas">
    <x-breadcrumb><a href="{{ route('courses.show', $course) }}">{{ $course->name }}</a><a href="{{ route('dosen.courses.assignments.index', $course) }}">Tugas</a><span>Tambah</span></x-breadcrumb>
    <x-page-header title="Tambah tugas" subtitle="Buat tugas untuk {{ $course->name }}." />
    <x-card>
        <form action="{{ route('dosen.courses.assignments.store', $course) }}" method="POST" class="form-stack">
            @csrf
            <x-form-field name="title" label="Judul tugas" :required="true">
                <x-input id="title" name="title" value="{{ old('title') }}" required />
            </x-form-field>
            <x-form-field name="instructions" label="Instruksi" :required="true">
                <x-textarea id="instructions" name="instructions" required>{{ old('instructions') }}</x-textarea>
            </x-form-field>
            <div class="form-grid">
                <x-form-field name="due_at" label="Batas waktu" :required="true">
                    <x-input id="due_at" name="due_at" type="datetime-local" value="{{ old('due_at') }}" required />
                </x-form-field>
                <x-form-field name="max_score" label="Nilai maksimal">
                    <x-input id="max_score" name="max_score" type="number" min="1" max="255" value="{{ old('max_score', 100) }}" />
                </x-form-field>
                <x-form-field name="status" label="Status" :required="true">
                    <x-select id="status" name="status" required>
                        <option value="draft" @selected(old('status', 'draft') === 'draft')>Draf</option>
                        <option value="published" @selected(old('status') === 'published')>Terbit</option>
                    </x-select>
                </x-form-field>
            </div>
            <div class="checkbox-row">
                <input id="allow_late" name="allow_late" type="checkbox" value="1" @checked(old('allow_late', true))>
                <label for="allow_late">Izinkan pengumpulan terlambat</label>
            </div>
            @error('allow_late')<p class="field-error">{{ $message }}</p>@enderror
            <div class="cluster">
                <x-button>Simpan tugas</x-button>
                <a class="button button--secondary" href="{{ route('dosen.courses.assignments.index', $course) }}">Batal</a>
            </div>
        </form>
    </x-card>
</x-layout>

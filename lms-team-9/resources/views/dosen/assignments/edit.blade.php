<x-layout title="Edit Tugas">
    <x-breadcrumb><a href="{{ route('courses.show', $assignment->course_id) }}">Mata kuliah</a><a href="{{ route('dosen.courses.assignments.index', $assignment->course_id) }}">Tugas</a><span>Ubah</span></x-breadcrumb>
    <x-page-header title="Edit tugas" subtitle="{{ $assignment->title }}" />
    <x-card>
        <form action="{{ route('dosen.assignments.update', $assignment) }}" method="POST" class="form-stack">
            @csrf
            @method('PUT')
            <x-form-field name="title" label="Judul tugas" :required="true">
                <x-input id="title" name="title" value="{{ old('title', $assignment->title) }}" required />
            </x-form-field>
            <x-form-field name="instructions" label="Instruksi" :required="true">
                <x-textarea id="instructions" name="instructions" required>{{ old('instructions', $assignment->instructions) }}</x-textarea>
            </x-form-field>
            <div class="form-grid">
                <x-form-field name="due_at" label="Batas waktu" :required="true">
                    <x-input id="due_at" name="due_at" type="datetime-local" value="{{ old('due_at', $assignment->due_at?->format('Y-m-d\\TH:i')) }}" required />
                </x-form-field>
                <x-form-field name="max_score" label="Nilai maksimal">
                    <x-input id="max_score" name="max_score" type="number" min="1" max="255" value="{{ old('max_score', $assignment->max_score) }}" />
                </x-form-field>
                <x-form-field name="status" label="Status" :required="true">
                    <x-select id="status" name="status" required>
                        <option value="draft" @selected(old('status', $assignment->status) === 'draft')>Draf</option>
                        <option value="published" @selected(old('status', $assignment->status) === 'published')>Terbit</option>
                    </x-select>
                </x-form-field>
            </div>
            <div class="checkbox-row">
                <input id="allow_late" name="allow_late" type="checkbox" value="1" @checked(old('allow_late', $assignment->allow_late))>
                <label for="allow_late">Izinkan pengumpulan terlambat</label>
            </div>
            @error('allow_late')<p class="field-error">{{ $message }}</p>@enderror
            <div class="cluster">
                <x-button>Perbarui tugas</x-button>
                <a class="button button--secondary" href="{{ route('dosen.assignments.show', $assignment) }}">Batal</a>
            </div>
        </form>
    </x-card>
</x-layout>

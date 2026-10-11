<x-layout title="Edit Tugas">
    <h1 class="mb-4 text-xl font-bold">Edit tugas — {{ $assignment->title }}</h1>
    <form action="{{ route('dosen.assignments.update', $assignment) }}" method="POST">
        @csrf
        @method('PUT')
        <label>Judul <input name="title" value="{{ old('title', $assignment->title) }}" required></label>
        @error('title') <p>{{ $message }}</p> @enderror
        <label>Instruksi <textarea name="instructions" required>{{ old('instructions', $assignment->instructions) }}</textarea></label>
        @error('instructions') <p>{{ $message }}</p> @enderror
        <label>Batas waktu <input type="datetime-local" name="due_at" value="{{ old('due_at', $assignment->due_at?->format('Y-m-d\\TH:i')) }}" required></label>
        @error('due_at') <p>{{ $message }}</p> @enderror
        <label>Nilai maksimal <input type="number" name="max_score" min="1" max="255" value="{{ old('max_score', $assignment->max_score) }}"></label>
        <label><input type="checkbox" name="allow_late" value="1" @checked(old('allow_late', $assignment->allow_late))> Izinkan terlambat</label>
        <label>Status
            <select name="status" required>
                <option value="draft" @selected(old('status', $assignment->status) === 'draft')>Draft</option>
                <option value="published" @selected(old('status', $assignment->status) === 'published')>Terbit</option>
            </select>
        </label>
        <button type="submit">Perbarui</button>
    </form>
</x-layout>

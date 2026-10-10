<x-layout title="Tambah Tugas">
    <h1 class="mb-4 text-xl font-bold">Tambah tugas — {{ $course->name }}</h1>
    <form action="{{ route('dosen.courses.assignments.store', $course) }}" method="POST">
        @csrf
        <label>Judul <input name="title" value="{{ old('title') }}" required></label>
        @error('title') <p>{{ $message }}</p> @enderror
        <label>Instruksi <textarea name="instructions" required>{{ old('instructions') }}</textarea></label>
        @error('instructions') <p>{{ $message }}</p> @enderror
        <label>Batas waktu <input type="datetime-local" name="due_at" value="{{ old('due_at') }}" required></label>
        @error('due_at') <p>{{ $message }}</p> @enderror
        <label>Nilai maksimal <input type="number" name="max_score" min="1" max="255" value="{{ old('max_score', 100) }}"></label>
        <label><input type="checkbox" name="allow_late" value="1" @checked(old('allow_late', true))> Izinkan terlambat</label>
        <label>Status
            <select name="status" required>
                <option value="draft">Draft</option>
                <option value="published">Terbit</option>
            </select>
        </label>
        <button type="submit">Simpan</button>
    </form>
</x-layout>

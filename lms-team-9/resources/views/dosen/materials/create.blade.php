<x-layout title="Tambah Materi">
    <h1 class="mb-4 text-xl font-bold">Tambah materi — {{ $course->name }}</h1>
    <form action="{{ route('dosen.courses.materials.store', $course) }}" method="POST" enctype="multipart/form-data">
        @csrf
        <label>Judul <input name="title" value="{{ old('title') }}" required></label>
        @error('title') <p>{{ $message }}</p> @enderror
        <label>Deskripsi <textarea name="description">{{ old('description') }}</textarea></label>
        <label>Jenis
            <select name="type" required>
                <option value="file">PDF/PPTX</option>
                <option value="link">Tautan</option>
            </select>
        </label>
        <label>File <input type="file" name="file" accept=".pdf,.pptx"></label>
        @error('file') <p>{{ $message }}</p> @enderror
        <label>URL <input type="url" name="external_url" value="{{ old('external_url') }}"></label>
        @error('external_url') <p>{{ $message }}</p> @enderror
        <button type="submit">Simpan</button>
    </form>
</x-layout>

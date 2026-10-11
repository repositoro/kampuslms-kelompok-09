<x-layout title="Edit Materi">
    <h1 class="mb-4 text-xl font-bold">Edit materi — {{ $material->title }}</h1>
    <form action="{{ route('dosen.materials.update', $material) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <label>Judul <input name="title" value="{{ old('title', $material->title) }}" required></label>
        @error('title') <p>{{ $message }}</p> @enderror
        <label>Deskripsi <textarea name="description">{{ old('description', $material->description) }}</textarea></label>
        <label>Jenis
            <select name="type" required>
                <option value="file" @selected(old('type', $material->type) === 'file')>PDF/PPTX</option>
                <option value="link" @selected(old('type', $material->type) === 'link')>Tautan</option>
            </select>
        </label>
        <label>File baru <input type="file" name="file" accept=".pdf,.pptx"></label>
        @error('file') <p>{{ $message }}</p> @enderror
        <label>URL <input type="url" name="external_url" value="{{ old('external_url', $material->external_url) }}"></label>
        @error('external_url') <p>{{ $message }}</p> @enderror
        <button type="submit">Perbarui</button>
    </form>
</x-layout>

<x-layout title="Edit Mata Kuliah">

    <div class="mb-6">
        <a href="{{ route('courses.index') }}"
           class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700">
            ← Kembali ke Daftar
        </a>
    </div>

    <div class="mx-auto max-w-2xl rounded-xl border border-gray-200 bg-white p-8 shadow-sm">
        <h1 class="mb-6 text-xl font-bold text-gray-900">
            Edit Mata Kuliah: <span class="text-slate-600">{{ $course->code }}</span>
        </h1>

        <form action="{{ route('courses.update', $course) }}" method="POST" novalidate>
            @csrf
            @method('PUT')

            {{-- Kode Mata Kuliah --}}
            <div class="mb-5">
                <label for="code" class="mb-1 block text-sm font-medium text-gray-700">
                    Kode Mata Kuliah <span class="text-red-500">*</span>
                </label>
                <input
                    id="code" type="text" name="code"
                    value="{{ old('code', $course->code) }}"
                    class="w-full rounded-md border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-500 @error('code') border-red-400 bg-red-50 @else border-gray-300 @enderror"
                >
                @error('code')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Nama Mata Kuliah --}}
            <div class="mb-5">
                <label for="name" class="mb-1 block text-sm font-medium text-gray-700">
                    Nama Mata Kuliah <span class="text-red-500">*</span>
                </label>
                <input
                    id="name" type="text" name="name"
                    value="{{ old('name', $course->name) }}"
                    class="w-full rounded-md border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-500 @error('name') border-red-400 bg-red-50 @else border-gray-300 @enderror"
                >
                @error('name')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Deskripsi --}}
            <div class="mb-5">
                <label for="description" class="mb-1 block text-sm font-medium text-gray-700">
                    Deskripsi
                </label>
                <textarea
                    id="description" name="description" rows="3"
                    class="w-full rounded-md border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-500 @error('description') border-red-400 bg-red-50 @else border-gray-300 @enderror"
                >{{ old('description', $course->description) }}</textarea>
                @error('description')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- SKS --}}
            <div class="mb-5">
                <label for="sks" class="mb-1 block text-sm font-medium text-gray-700">
                    Jumlah SKS <span class="text-red-500">*</span>
                </label>
                <input
                    id="sks" type="number" name="sks" min="1" max="6"
                    value="{{ old('sks', $course->sks) }}"
                    class="w-28 rounded-md border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-500 @error('sks') border-red-400 bg-red-50 @else border-gray-300 @enderror"
                >
                @error('sks')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Dosen Pengampu --}}
            <div class="mb-5">
                <label for="lecturer_id" class="mb-1 block text-sm font-medium text-gray-700">
                    Dosen Pengampu <span class="text-red-500">*</span>
                </label>
                <select
                    id="lecturer_id" name="lecturer_id"
                    class="w-full rounded-md border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-500 @error('lecturer_id') border-red-400 bg-red-50 @else border-gray-300 @enderror"
                >
                    <option value="">-- Pilih Dosen --</option>
                    @foreach ($lecturers as $lecturer)
                        <option value="{{ $lecturer->id }}"
                                {{ old('lecturer_id', $course->lecturer_id) == $lecturer->id ? 'selected' : '' }}>
                            {{ $lecturer->name }}
                        </option>
                    @endforeach
                </select>
                @error('lecturer_id')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Status --}}
            <div class="mb-7">
                <label for="status" class="mb-1 block text-sm font-medium text-gray-700">
                    Status <span class="text-red-500">*</span>
                </label>
                <select
                    id="status" name="status"
                    class="w-full rounded-md border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-500 @error('status') border-red-400 bg-red-50 @else border-gray-300 @enderror"
                >
                    <option value="draft"    {{ old('status', $course->status) == 'draft'    ? 'selected' : '' }}>Draft</option>
                    <option value="active"   {{ old('status', $course->status) == 'active'   ? 'selected' : '' }}>Aktif</option>
                    <option value="archived" {{ old('status', $course->status) == 'archived' ? 'selected' : '' }}>Diarsipkan</option>
                </select>
                @error('status')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Tombol Aksi --}}
            <div class="flex items-center gap-3">
                <button type="submit"
                        class="rounded-md bg-slate-800 px-5 py-2 text-sm font-semibold text-white hover:bg-slate-700">
                    Perbarui
                </button>
                <a href="{{ route('courses.index') }}"
                   class="rounded-md border border-gray-300 px-5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Batal
                </a>
            </div>
        </form>
    </div>

</x-layout>
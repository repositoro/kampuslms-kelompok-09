<x-layout title="Tambah Mata Kuliah">
    <div style="max-width: 640px; margin: 0 auto; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 2rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <h1 style="font-size: 1.5rem; font-weight: 700; margin-top: 0; margin-bottom: 0.5rem; color: #1e293b;">
            Tambah Mata Kuliah Baru
        </h1>
        <p style="color: #64748b; font-size: 0.875rem; margin-bottom: 1.5rem;">
            Isi formulir berikut untuk mendaftarkan mata kuliah baru ke sistem.
        </p>

        <form action="{{ route('courses.store') }}" method="POST">
            @csrf

            <div style="margin-bottom: 1.25rem;">
                <label for="code" style="display: block; font-weight: 600; font-size: 0.875rem; margin-bottom: 0.25rem; color: #334155;">
                    Kode Mata Kuliah <span style="color: #ef4444;">*</span>
                </label>
                <input type="text" 
                       id="code" 
                       name="code" 
                       value="{{ old('code') }}" 
                       placeholder="Contoh: SI2514024" 
                       style="width: 100%; padding: 0.6rem 0.75rem; border: 1px solid {{ $errors->has('code') ? '#ef4444' : '#cbd5e1' }}; border-radius: 6px; font-size: 0.875rem; box-sizing: border-box;">
                @error('code')
                    <p style="color: #ef4444; font-size: 0.75rem; margin: 0.25rem 0 0 0;">{{ $message }}</p>
                @enderror
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label for="name" style="display: block; font-weight: 600; font-size: 0.875rem; margin-bottom: 0.25rem; color: #334155;">
                    Nama Mata Kuliah <span style="color: #ef4444;">*</span>
                </label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       value="{{ old('name') }}" 
                       placeholder="Contoh: Pemrograman Web Lanjut" 
                       style="width: 100%; padding: 0.6rem 0.75rem; border: 1px solid {{ $errors->has('name') ? '#ef4444' : '#cbd5e1' }}; border-radius: 6px; font-size: 0.875rem; box-sizing: border-box;">
                @error('name')
                    <p style="color: #ef4444; font-size: 0.75rem; margin: 0.25rem 0 0 0;">{{ $message }}</p>
                @enderror
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label for="description" style="display: block; font-weight: 600; font-size: 0.875rem; margin-bottom: 0.25rem; color: #334155;">
                    Deskripsi
                </label>
                <textarea id="description" 
                          name="description" 
                          rows="3" 
                          placeholder="Deskripsi singkat mengenai capaian mata kuliah..." 
                          style="width: 100%; padding: 0.6rem 0.75rem; border: 1px solid {{ $errors->has('description') ? '#ef4444' : '#cbd5e1' }}; border-radius: 6px; font-size: 0.875rem; box-sizing: border-box;">{{ old('description') }}</textarea>
                @error('description')
                    <p style="color: #ef4444; font-size: 0.75rem; margin: 0.25rem 0 0 0;">{{ $message }}</p>
                @enderror
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label for="sks" style="display: block; font-weight: 600; font-size: 0.875rem; margin-bottom: 0.25rem; color: #334155;">
                    Bobot SKS (1–6) <span style="color: #ef4444;">*</span>
                </label>
                <input type="number" 
                       id="sks" 
                       name="sks" 
                       min="1" 
                       max="6" 
                       value="{{ old('sks', 3) }}" 
                       style="width: 100%; padding: 0.6rem 0.75rem; border: 1px solid {{ $errors->has('sks') ? '#ef4444' : '#cbd5e1' }}; border-radius: 6px; font-size: 0.875rem; box-sizing: border-box;">
                @error('sks')
                    <p style="color: #ef4444; font-size: 0.75rem; margin: 0.25rem 0 0 0;">{{ $message }}</p>
                @enderror
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label for="lecturer_id" style="display: block; font-weight: 600; font-size: 0.875rem; margin-bottom: 0.25rem; color: #334155;">
                    Dosen Pengampu <span style="color: #ef4444;">*</span>
                </label>
                <select id="lecturer_id" 
                        name="lecturer_id" 
                        style="width: 100%; padding: 0.6rem 0.75rem; border: 1px solid {{ $errors->has('lecturer_id') ? '#ef4444' : '#cbd5e1' }}; border-radius: 6px; font-size: 0.875rem; box-sizing: border-box; background-color: #ffffff;">
                    <option value="">-- Pilih Dosen Pengampu --</option>
                    @foreach ($lecturers as $lecturer)
                        <option value="{{ $lecturer->id }}" {{ old('lecturer_id') == $lecturer->id ? 'selected' : '' }}>
                            {{ $lecturer->name }} ({{ $lecturer->email }})
                        </option>
                    @endforeach
                </select>
                @error('lecturer_id')
                    <p style="color: #ef4444; font-size: 0.75rem; margin: 0.25rem 0 0 0;">{{ $message }}</p>
                @enderror
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label for="status" style="display: block; font-weight: 600; font-size: 0.875rem; margin-bottom: 0.25rem; color: #334155;">
                    Status <span style="color: #ef4444;">*</span>
                </label>
                <select id="status" 
                        name="status" 
                        style="width: 100%; padding: 0.6rem 0.75rem; border: 1px solid {{ $errors->has('status') ? '#ef4444' : '#cbd5e1' }}; border-radius: 6px; font-size: 0.875rem; box-sizing: border-box; background-color: #ffffff;">
                    <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="archived" {{ old('status') == 'archived' ? 'selected' : '' }}>Archived</option>
                </select>
                @error('status')
                    <p style="color: #ef4444; font-size: 0.75rem; margin: 0.25rem 0 0 0;">{{ $message }}</p>
                @enderror
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.75rem; align-items: center; border-top: 1px solid #e2e8f0; padding-top: 1.25rem;">
                <a href="{{ route('courses.index') }}" 
                   style="color: #64748b; text-decoration: none; font-size: 0.875rem; font-weight: 500; padding: 0.5rem 1rem;">
                    Batal
                </a>
                <button type="submit" 
                        style="background-color: #2563eb; color: #ffffff; border: none; padding: 0.6rem 1.25rem; border-radius: 6px; font-size: 0.875rem; font-weight: 600; cursor: pointer;">
                    Simpan Mata Kuliah
                </button>
            </div>
        </form>
    </div>
</x-layout>
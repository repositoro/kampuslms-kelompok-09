<x-layout title="Tambah Pengguna">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 2rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <h1 style="font-size: 1.5rem; font-weight: 700; margin-top: 0; margin-bottom: 0.5rem; color: #1e293b;">
            Tambah Pengguna Baru
        </h1>
        <p style="color: #64748b; font-size: 0.875rem; margin-bottom: 1.5rem;">
            Daftarkan akun pengguna baru ke dalam LMS kampus.
        </p>

        <form action="{{ route('users.store') }}" method="POST">
            @csrf

            <div style="margin-bottom: 1.25rem;">
                <label for="name" style="display: block; font-weight: 600; font-size: 0.875rem; margin-bottom: 0.25rem; color: #334155;">
                    Nama Lengkap <span style="color: #ef4444;">*</span>
                </label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       value="{{ old('name') }}" 
                       placeholder="Contoh: Vera Friska" 
                       style="width: 100%; padding: 0.6rem 0.75rem; border: 1px solid {{ $errors->has('name') ? '#ef4444' : '#cbd5e1' }}; border-radius: 6px; font-size: 0.875rem; box-sizing: border-box;">
                @error('name')
                    <p style="color: #ef4444; font-size: 0.75rem; margin: 0.25rem 0 0 0;">{{ $message }}</p>
                @enderror
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label for="email" style="display: block; font-weight: 600; font-size: 0.875rem; margin-bottom: 0.25rem; color: #334155;">
                    Alamat Email <span style="color: #ef4444;">*</span>
                </label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       value="{{ old('email') }}" 
                       placeholder="Contoh: user@kampus.ac.id" 
                       style="width: 100%; padding: 0.6rem 0.75rem; border: 1px solid {{ $errors->has('email') ? '#ef4444' : '#cbd5e1' }}; border-radius: 6px; font-size: 0.875rem; box-sizing: border-box;">
                @error('email')
                    <p style="color: #ef4444; font-size: 0.75rem; margin: 0.25rem 0 0 0;">{{ $message }}</p>
                @enderror
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label for="nim_nip" style="display: block; font-weight: 600; font-size: 0.875rem; margin-bottom: 0.25rem; color: #334155;">
                    NIM / NIP
                </label>
                <input type="text" 
                       id="nim_nip" 
                       name="nim_nip" 
                       value="{{ old('nim_nip') }}" 
                       placeholder="Nomor Induk Mahasiswa atau Pegawai" 
                       style="width: 100%; padding: 0.6rem 0.75rem; border: 1px solid {{ $errors->has('nim_nip') ? '#ef4444' : '#cbd5e1' }}; border-radius: 6px; font-size: 0.875rem; box-sizing: border-box;">
                @error('nim_nip')
                    <p style="color: #ef4444; font-size: 0.75rem; margin: 0.25rem 0 0 0;">{{ $message }}</p>
                @enderror
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label for="password" style="display: block; font-weight: 600; font-size: 0.875rem; margin-bottom: 0.25rem; color: #334155;">
                    Password <span style="color: #ef4444;">*</span>
                </label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       placeholder="Minimal 8 karakter" 
                       style="width: 100%; padding: 0.6rem 0.75rem; border: 1px solid {{ $errors->has('password') ? '#ef4444' : '#cbd5e1' }}; border-radius: 6px; font-size: 0.875rem; box-sizing: border-box;">
                @error('password')
                    <p style="color: #ef4444; font-size: 0.75rem; margin: 0.25rem 0 0 0;">{{ $message }}</p>
                @enderror
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label for="role" style="display: block; font-weight: 600; font-size: 0.875rem; margin-bottom: 0.25rem; color: #334155;">
                    Peran (Role) <span style="color: #ef4444;">*</span>
                </label>
                <select id="role" 
                        name="role" 
                        style="width: 100%; padding: 0.6rem 0.75rem; border: 1px solid {{ $errors->has('role') ? '#ef4444' : '#cbd5e1' }}; border-radius: 6px; font-size: 0.875rem; box-sizing: border-box; background-color: #ffffff;">
                    <option value="">-- Pilih Role Pengguna --</option>
                    <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="dosen" {{ old('role') === 'dosen' ? 'selected' : '' }}>Dosen</option>
                    <option value="mahasiswa" {{ old('role') === 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                </select>
                @error('role')
                    <p style="color: #ef4444; font-size: 0.75rem; margin: 0.25rem 0 0 0;">{{ $message }}</p>
                @enderror
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.75rem; align-items: center; border-top: 1px solid #e2e8f0; padding-top: 1.25rem;">
                <a href="{{ route('users.index') }}" 
                   style="color: #64748b; text-decoration: none; font-size: 0.875rem; font-weight: 500; padding: 0.5rem 1rem;">
                    Batal
                </a>
                <button type="submit" 
                        style="background-color: #2563eb; color: #ffffff; border: none; padding: 0.6rem 1.25rem; border-radius: 6px; font-size: 0.875rem; font-weight: 600; cursor: pointer;">
                    Simpan Pengguna
                </button>
            </div>
        </form>
    </div>
</x-layout>
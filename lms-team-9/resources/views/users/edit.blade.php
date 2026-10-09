<x-layout title="Edit Pengguna">

    <div class="mb-6">
        <a href="{{ route('admin.users.index') }}"
           class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700">
            ← Kembali ke Daftar
        </a>
    </div>

    <div class="mx-auto max-w-2xl rounded-xl border border-gray-200 bg-white p-8 shadow-sm">
        <h1 class="mb-6 text-xl font-bold text-gray-900">
            Edit Pengguna: <span class="text-slate-600">{{ $user->name }}</span>
        </h1>

        <form action="{{ route('admin.users.update', $user) }}" method="POST" novalidate>
            @csrf
            @method('PUT')

            {{-- Nama Lengkap --}}
            <div class="mb-5">
                <label for="name" class="mb-1 block text-sm font-medium text-gray-700">
                    Nama Lengkap <span class="text-red-500">*</span>
                </label>
                <input
                    id="name" type="text" name="name"
                    value="{{ old('name', $user->name) }}"
                    class="w-full rounded-md border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-500 @error('name') border-red-400 bg-red-50 @else border-gray-300 @enderror"
                >
                @error('name')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Email --}}
            <div class="mb-5">
                <label for="email" class="mb-1 block text-sm font-medium text-gray-700">
                    Alamat Email <span class="text-red-500">*</span>
                </label>
                <input
                    id="email" type="email" name="email"
                    value="{{ old('email', $user->email) }}"
                    class="w-full rounded-md border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-500 @error('email') border-red-400 bg-red-50 @else border-gray-300 @enderror"
                >
                @error('email')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Password (opsional) --}}
            <div class="mb-5">
                <label for="password" class="mb-1 block text-sm font-medium text-gray-700">
                    Kata Sandi Baru
                    <span class="ml-1 text-xs font-normal text-gray-400">(kosongkan jika tidak ingin diubah)</span>
                </label>
                <input
                    id="password" type="password" name="password"
                    placeholder="Minimal 8 karakter"
                    class="w-full rounded-md border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-500 @error('password') border-red-400 bg-red-50 @else border-gray-300 @enderror"
                >
                @error('password')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Role --}}
            <div class="mb-7">
                <label for="role" class="mb-1 block text-sm font-medium text-gray-700">
                    Peran <span class="text-red-500">*</span>
                </label>
                <select
                    id="role" name="role"
                    class="w-full rounded-md border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-500 @error('role') border-red-400 bg-red-50 @else border-gray-300 @enderror"
                >
                    <option value="admin"     {{ old('role', $user->role) == 'admin'     ? 'selected' : '' }}>Admin</option>
                    <option value="dosen"     {{ old('role', $user->role) == 'dosen'     ? 'selected' : '' }}>Dosen</option>
                    <option value="mahasiswa" {{ old('role', $user->role) == 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                </select>
                @error('role')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Tombol Aksi --}}
            <div class="flex items-center gap-3">
                <button type="submit"
                        class="rounded-md bg-slate-800 px-5 py-2 text-sm font-semibold text-white hover:bg-slate-700">
                    Perbarui
                </button>
                <a href="{{ route('admin.users.index') }}"
                   class="rounded-md border border-gray-300 px-5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Batal
                </a>
            </div>
        </form>
    </div>

</x-layout>

<x-layout title="Tambah Pengguna">

    <div class="space-y-6">

        <!-- Back Link -->
        <a href="{{ route('users.index') }}"
           class="inline-flex items-center text-sm font-semibold text-blue-600 hover:text-blue-700 transition-colors">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali ke Daftar
        </a>

        <!-- Form Card -->
        <div class="mx-auto max-w-2xl">
            <div class="bg-white rounded-2xl p-8 md:p-10 shadow-sm border border-slate-100">
                <h1 class="text-2xl font-extrabold text-slate-800 mb-6">Tambah Pengguna Baru</h1>

                <form action="{{ route('users.store') }}" method="POST" novalidate class="space-y-6">
                    @csrf

                    {{-- Nama Lengkap --}}
                    <div class="space-y-2">
                        <label for="name" class="block text-sm font-semibold text-slate-700">
                            Nama Lengkap <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="name" type="text" name="name"
                            value="{{ old('name') }}"
                            placeholder="Contoh: Budi Santoso"
                            class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:bg-white transition-all @error('name') border-red-400 bg-red-50 @enderror"
                        >
                        @error('name')
                            <p class="text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div class="space-y-2">
                        <label for="email" class="block text-sm font-semibold text-slate-700">
                            Alamat Email <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="email" type="email" name="email"
                            value="{{ old('email') }}"
                            placeholder="contoh@email.com"
                            class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:bg-white transition-all @error('email') border-red-400 bg-red-50 @enderror"
                        >
                        @error('email')
                            <p class="text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div class="space-y-2">
                        <label for="password" class="block text-sm font-semibold text-slate-700">
                            Kata Sandi <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="password" type="password" name="password"
                            placeholder="Minimal 8 karakter"
                            class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:bg-white transition-all @error('password') border-red-400 bg-red-50 @enderror"
                        >
                        @error('password')
                            <p class="text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Role --}}
                    <div class="space-y-2">
                        <label for="role" class="block text-sm font-semibold text-slate-700">
                            Peran <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="role" name="role"
                            class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:bg-white transition-all @error('role') border-red-400 bg-red-50 @enderror"
                        >
                            <option value="">-- Pilih Peran --</option>
                            <option value="admin"     {{ old('role') == 'admin'     ? 'selected' : '' }}>Admin</option>
                            <option value="dosen"     {{ old('role') == 'dosen'     ? 'selected' : '' }}>Dosen</option>
                            <option value="mahasiswa" {{ old('role') == 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                        </select>
                        @error('role')
                            <p class="text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex items-center gap-3 pt-4">
                        <button type="submit"
                                class="flex-1 py-3.5 rounded-2xl bg-blue-800 text-white font-bold text-sm shadow-sm hover:bg-blue-900 hover:shadow-md transition-all">
                            Simpan Pengguna
                        </button>
                        <a href="{{ route('users.index') }}"
                           class="px-6 py-3.5 rounded-2xl border border-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>

    </div>

</x-layout>
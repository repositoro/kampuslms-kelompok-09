<x-layout title="Tambah Pengguna">
    <x-breadcrumb><a href="{{ route('admin.users.index') }}">Pengguna</a><span>Tambah akun</span></x-breadcrumb>
    <x-page-header title="Tambah pengguna" subtitle="Buat akun dan tetapkan peran." />
    <x-card>
        <form action="{{ route('admin.users.store') }}" method="POST" class="form-stack">
            @csrf
            <div class="form-grid">
                <x-form-field name="name" label="Nama lengkap" :required="true">
                    <x-input id="name" name="name" value="{{ old('name') }}" autocomplete="name" required />
                </x-form-field>
                <x-form-field name="email" label="Alamat email" :required="true">
                    <x-input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required />
                </x-form-field>
                <x-form-field name="nim_nip" label="NIM/NIP">
                    <x-input id="nim_nip" name="nim_nip" value="{{ old('nim_nip') }}" />
                </x-form-field>
                <x-form-field name="role" label="Peran" :required="true">
                    <x-select id="role" name="role" required>
                        <option value="">Pilih peran</option>
                        <option value="admin" @selected(old('role') === 'admin')>Admin</option>
                        <option value="dosen" @selected(old('role') === 'dosen')>Dosen</option>
                        <option value="mahasiswa" @selected(old('role') === 'mahasiswa')>Mahasiswa</option>
                    </x-select>
                </x-form-field>
                <x-form-field name="password" label="Kata sandi" hint="Minimal 8 karakter." :required="true">
                    <x-input id="password" name="password" type="password" autocomplete="new-password" required />
                </x-form-field>
            </div>
            <div class="cluster">
                <x-button>Simpan pengguna</x-button>
                <a class="button button--secondary" href="{{ route('admin.users.index') }}">Batal</a>
            </div>
        </form>
    </x-card>
</x-layout>

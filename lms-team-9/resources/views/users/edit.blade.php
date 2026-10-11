<x-layout title="Edit Pengguna">
    <x-breadcrumb><a href="{{ route('admin.users.index') }}">Pengguna</a><a href="{{ route('admin.users.show', $user) }}">{{ $user->name }}</a><span>Ubah</span></x-breadcrumb>
    <x-page-header title="Edit pengguna" subtitle="Perbarui identitas dan peran akun." />
    <x-card>
        <form action="{{ route('admin.users.update', $user) }}" method="POST" class="form-stack">
            @csrf
            @method('PUT')
            <div class="form-grid">
                <x-form-field name="name" label="Nama lengkap" :required="true">
                    <x-input id="name" name="name" value="{{ old('name', $user->name) }}" required />
                </x-form-field>
                <x-form-field name="email" label="Alamat email" :required="true">
                    <x-input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required />
                </x-form-field>
                <x-form-field name="nim_nip" label="NIM/NIP">
                    <x-input id="nim_nip" name="nim_nip" value="{{ old('nim_nip', $user->nim_nip) }}" />
                </x-form-field>
                <x-form-field name="role" label="Peran" :required="true">
                    <x-select id="role" name="role" required>
                        <option value="admin" @selected(old('role', $user->role) === 'admin')>Admin</option>
                        <option value="dosen" @selected(old('role', $user->role) === 'dosen')>Dosen</option>
                        <option value="mahasiswa" @selected(old('role', $user->role) === 'mahasiswa')>Mahasiswa</option>
                    </x-select>
                </x-form-field>
                <x-form-field name="password" label="Kata sandi baru" hint="Kosongkan jika tidak ingin mengubah kata sandi.">
                    <x-input id="password" name="password" type="password" autocomplete="new-password" />
                </x-form-field>
            </div>
            <div class="cluster">
                <x-button>Perbarui pengguna</x-button>
                <a class="button button--secondary" href="{{ route('admin.users.show', $user) }}">Batal</a>
            </div>
        </form>
    </x-card>
</x-layout>

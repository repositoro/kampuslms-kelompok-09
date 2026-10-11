<x-layout title="Pengguna">
    <x-page-header title="Pengguna" subtitle="Kelola akun dan peran pengguna KampusLMS.">
        <x-slot:actions><a class="button button--primary" href="{{ route('admin.users.create') }}">Tambah pengguna</a></x-slot:actions>
    </x-page-header>
    <x-card>
        <form action="{{ route('admin.users.index') }}" method="GET" class="form-grid">
            <x-form-field name="search" label="Cari nama atau email">
                <x-input id="search" name="search" type="search" value="{{ request('search') }}" placeholder="Ketik nama atau email" />
            </x-form-field>
            <x-form-field name="role" label="Filter peran">
                <x-select id="role" name="role">
                    <option value="">Semua peran</option>
                    <option value="admin" @selected(request('role') === 'admin')>Admin</option>
                    <option value="dosen" @selected(request('role') === 'dosen')>Dosen</option>
                    <option value="mahasiswa" @selected(request('role') === 'mahasiswa')>Mahasiswa</option>
                </x-select>
            </x-form-field>
            <div class="cluster">
                <x-button>Cari pengguna</x-button>
                <a class="button button--secondary" href="{{ route('admin.users.index') }}">Atur ulang</a>
            </div>
        </form>
    </x-card>
    <x-card title="Daftar pengguna">
        <x-table>
            <thead><tr><th>Nama</th><th>Email</th><th>NIM/NIP</th><th>Peran</th><th>Aksi</th></tr></thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td data-label="Nama"><a href="{{ route('admin.users.show', $user) }}"><strong>{{ $user->name }}</strong></a></td>
                        <td data-label="Email">{{ $user->email }}</td>
                        <td data-label="NIM/NIP">{{ $user->nim_nip ?? '—' }}</td>
                        <td data-label="Peran"><x-badge :variant="$user->role === 'admin' ? 'info' : 'neutral'">{{ ucfirst($user->role) }}</x-badge></td>
                        <td data-label="Aksi">
                            <div class="record-actions">
                                <a href="{{ route('admin.users.edit', $user) }}">Ubah</a>
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                                    onsubmit="return confirm('Hapus pengguna {{ $user->name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="button button--danger button--small" type="submit">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5"><x-empty-state title="Belum ada data pengguna" /></td></tr>
                @endforelse
            </tbody>
        </x-table>
        {{ $users->links('vendor.pagination.simple') }}
    </x-card>
</x-layout>

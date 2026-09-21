<x-layout title="Daftar Pengguna">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 style="font-size: 1.75rem; font-weight: 700; margin: 0; color: #1e293b;">Daftar Pengguna</h1>
            <p style="margin: 0.25rem 0 0 0; color: #64748b; font-size: 0.875rem;">Kelola data pengguna sistem (Admin, Dosen, dan Mahasiswa).</p>
        </div>
        <div>
            <a href="{{ route('users.create') }}" 
               style="background-color: #2563eb; color: #ffffff; padding: 0.5rem 1rem; border-radius: 6px; text-decoration: none; font-weight: 500; font-size: 0.875rem; display: inline-flex; align-items: center; gap: 0.5rem;">
                + Tambah Pengguna
            </a>
        </div>
    </div>

    {{-- Filter dan Pencarian Pengguna --}}
    <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1rem; margin-bottom: 1.5rem;">
        <form method="GET" action="{{ route('users.index') }}" style="display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: center;">
            <div style="flex: 1; min-width: 220px;">
                <input type="text" 
                       name="q" 
                       value="{{ request('q') }}" 
                       placeholder="Cari nama, email, atau NIM/NIP..." 
                       style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.875rem; box-sizing: border-box;">
            </div>
            <div style="min-width: 160px;">
                <select name="role" style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.875rem; box-sizing: border-box; background-color: #ffffff;">
                    <option value="">-- Semua Role --</option>
                    <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="dosen" {{ request('role') === 'dosen' ? 'selected' : '' }}>Dosen</option>
                    <option value="mahasiswa" {{ request('role') === 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                </select>
            </div>
            <div style="display: flex; gap: 0.5rem;">
                <button type="submit" 
                        style="background-color: #334155; color: #ffffff; border: none; padding: 0.5rem 1rem; border-radius: 6px; font-size: 0.875rem; font-weight: 500; cursor: pointer;">
                    Filter & Cari
                </button>
                @if (request()->hasAny(['q', 'role']))
                    <a href="{{ route('users.index') }}" 
                       style="background-color: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; padding: 0.5rem 0.75rem; border-radius: 6px; font-size: 0.875rem; text-decoration: none; display: inline-flex; align-items: center;">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Tabel Pengguna --}}
    <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.875rem;">
            <thead>
                <tr style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0; color: #475569;">
                    <th style="padding: 0.75rem 1rem; font-weight: 600;">Nama</th>
                    <th style="padding: 0.75rem 1rem; font-weight: 600;">Email</th>
                    <th style="padding: 0.75rem 1rem; font-weight: 600;">NIM / NIP</th>
                    <th style="padding: 0.75rem 1rem; font-weight: 600;">Role</th>
                    <th style="padding: 0.75rem 1rem; font-weight: 600; text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 0.75rem 1rem; font-weight: 600; color: #1e293b;">
                            <a href="{{ route('users.show', $user) }}" style="color: #2563eb; text-decoration: none;">
                                {{ $user->name }}
                            </a>
                        </td>
                        <td style="padding: 0.75rem 1rem; color: #475569;">
                            {{ $user->email }}
                        </td>
                        <td style="padding: 0.75rem 1rem; font-family: monospace; color: #64748b;">
                            {{ $user->nim_nip ?? '-' }}
                        </td>
                        <td style="padding: 0.75rem 1rem;">
                            @if ($user->role === 'admin')
                                <span style="background-color: #fee2e2; color: #991b1b; padding: 0.2rem 0.5rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600;">Admin</span>
                            @elseif ($user->role === 'dosen')
                                <span style="background-color: #e0e7ff; color: #3730a3; padding: 0.2rem 0.5rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600;">Dosen</span>
                            @else
                                <span style="background-color: #f1f5f9; color: #334155; padding: 0.2rem 0.5rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600;">Mahasiswa</span>
                            @endif
                        </td>
                        <td style="padding: 0.75rem 1rem; text-align: right; white-space: nowrap;">
                            <a href="{{ route('users.edit', $user) }}" 
                               style="color: #d97706; text-decoration: none; font-weight: 500; margin-right: 0.75rem;">
                                Edit
                            </a>
                            <form action="{{ route('users.destroy', $user) }}" 
                                  method="POST" 
                                  style="display:inline;" 
                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengguna {{ $user->name }}?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        style="background: none; border: none; color: #dc2626; cursor: pointer; font-size: 0.875rem; font-weight: 500; padding: 0;">
                                    Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 2rem 1rem; color: #94a3b8;">
                            Tidak ada pengguna yang cocok dengan kriteria pencarian.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination dengan Query String --}}
    <div style="margin-top: 1.5rem;">
        {{ $users->links() }}
    </div>
</x-layout>
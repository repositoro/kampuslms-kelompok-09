<x-layout title="Daftar Pengguna">

    <div class="space-y-6">

        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-800">Daftar Pengguna</h1>
                <p class="text-slate-500 mt-1">Kelola semua pengguna yang terdaftar di sistem.</p>
            </div>
            <a href="{{ route('users.create') }}"
               class="inline-flex items-center gap-2 rounded-2xl bg-blue-800 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-900 hover:shadow-md transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Tambah Pengguna
            </a>
        </div>

        <!-- Search & Filter -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
            <form method="GET" action="{{ route('users.index') }}" class="flex flex-wrap items-center gap-3">
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Cari nama atau email…"
                    class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:bg-white w-64 transition-all"
                >
                <select name="role"
                        class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:bg-white transition-all">
                    <option value="">Semua Peran</option>
                    <option value="admin"     {{ request('role') == 'admin'     ? 'selected' : '' }}>Admin</option>
                    <option value="dosen"     {{ request('role') == 'dosen'     ? 'selected' : '' }}>Dosen</option>
                    <option value="mahasiswa" {{ request('role') == 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                </select>
                <button type="submit"
                        class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 transition-colors">
                    Cari
                </button>
                @if (request('search') || request('role'))
                    <a href="{{ route('users.index') }}"
                       class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">
                       Reset
                    </a>
                @endif
            </form>
        </div>

        <!-- Table Card -->
        <div class="overflow-x-auto rounded-2xl border border-slate-100 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-6 py-4">Pengguna</th>
                        <th class="px-6 py-4">Email</th>
                        <th class="px-6 py-4 text-center">Peran</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 text-slate-700">
                    @forelse ($users as $user)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-blue-800 flex items-center justify-center text-sm font-bold text-white shrink-0 shadow-sm">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <a href="{{ route('users.show', $user) }}"
                                       class="font-semibold text-slate-800 hover:text-blue-600 transition-colors">
                                        {{ $user->name }}
                                    </a>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-slate-500">{{ $user->email }}</td>
                            <td class="px-6 py-4 text-center">
                                @php
                                    $badge = match($user->role) {
                                        'admin'     => 'bg-blue-50 text-blue-900',
                                        'dosen'     => 'bg-blue-50 text-blue-700',
                                        default     => 'bg-slate-100 text-slate-600',
                                    };
                                    $label = match($user->role) {
                                        'admin'     => 'Admin',
                                        'dosen'     => 'Dosen',
                                        default     => 'Mahasiswa',
                                    };
                                @endphp
                                <span class="inline-block rounded-full px-3 py-1 text-xs font-semibold {{ $badge }}">
                                    {{ $label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="inline-flex items-center gap-3">
                                    <a href="{{ route('users.edit', $user) }}"
                                       class="text-sm font-semibold text-blue-600 hover:text-blue-700 transition-colors">
                                        Edit
                                    </a>
                                    <button type="button"
                                            onclick="bukaModalHapus('{{ route('users.destroy', $user) }}', '{{ addslashes($user->name) }}')"
                                            class="text-sm font-semibold text-red-500 hover:text-red-700 transition-colors">
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center text-slate-300 mb-3">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    </div>
                                    <p class="font-medium">Belum ada data pengguna.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($users->hasPages())
            <div class="mt-2">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    {{-- Modal Konfirmasi Hapus --}}
    <div id="modal-hapus"
         class="fixed inset-0 z-50 hidden items-center justify-center"
         role="dialog" aria-modal="true" aria-labelledby="modal-judul">

        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="tutupModalHapus()"></div>

        <div class="relative mx-4 w-full max-w-sm rounded-2xl bg-white p-7 shadow-2xl border border-slate-100">
            <div class="mb-4 flex items-center gap-3">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-red-50">
                    <svg class="h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                </div>
                <h3 id="modal-judul" class="text-base font-bold text-slate-800">Konfirmasi Penghapusan</h3>
            </div>
            <p class="mb-6 text-sm text-slate-600 leading-relaxed">
                Apakah Anda yakin ingin menghapus pengguna
                <strong id="modal-nama" class="text-slate-800"></strong>?
                Tindakan ini tidak dapat dibatalkan.
            </p>
            <div class="flex justify-end gap-3">
                <button type="button"
                        onclick="tutupModalHapus()"
                        class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                    Batal
                </button>
                <form id="form-hapus" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="rounded-xl bg-red-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-red-700 transition-colors shadow-sm">
                        Ya, Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function bukaModalHapus(actionUrl, nama) {
            document.getElementById('form-hapus').action = actionUrl;
            document.getElementById('modal-nama').textContent = nama;
            const modal = document.getElementById('modal-hapus');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
        function tutupModalHapus() {
            const modal = document.getElementById('modal-hapus');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') tutupModalHapus();
        });
    </script>

</x-layout>
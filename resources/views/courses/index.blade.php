<x-layout title="Daftar Mata Kuliah">

    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Daftar Mata Kuliah</h1>
        <a href="{{ route('courses.create') }}"
           class="inline-flex items-center gap-1.5 rounded-md bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
            + Tambah Mata Kuliah
        </a>
    </div>

    {{-- Form Pencarian & Filter --}}
    <form method="GET" action="{{ route('courses.index') }}"
          class="mb-5 flex flex-wrap items-center gap-3">
        <input
            type="text"
            name="search"
            value="{{ request('search') }}"
            placeholder="Cari kode atau nama…"
            class="rounded-md border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-500 w-60"
        >
        <select name="status"
                class="rounded-md border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-500">
            <option value="">Semua Status</option>
            <option value="draft"    {{ request('status') == 'draft'    ? 'selected' : '' }}>Draft</option>
            <option value="active"   {{ request('status') == 'active'   ? 'selected' : '' }}>Aktif</option>
            <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Diarsipkan</option>
        </select>
        <button type="submit"
                class="rounded-md bg-slate-700 px-4 py-2 text-sm font-medium text-white hover:bg-slate-600">
            Cari
        </button>
        @if (request('search') || request('status'))
            <a href="{{ route('courses.index') }}"
               class="rounded-md border border-gray-300 px-4 py-2 text-sm text-gray-600 hover:bg-gray-100">
               Reset
            </a>
        @endif
    </form>

    {{-- Tabel --}}
    <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                <tr>
                    <th class="px-4 py-3">Kode</th>
                    <th class="px-4 py-3">Nama Mata Kuliah</th>
                    <th class="px-4 py-3 text-center">SKS</th>
                    <th class="px-4 py-3">Dosen Pengampu</th>
                    <th class="px-4 py-3 text-center">Status</th>
                    <th class="px-4 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-gray-700">
                @forelse ($courses as $course)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono font-semibold text-slate-700">{{ $course->code }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ route('courses.show', $course) }}"
                               class="font-medium text-slate-800 hover:text-slate-600 hover:underline">
                                {{ $course->name }}
                            </a>
                        </td>
                        <td class="px-4 py-3 text-center">{{ $course->sks }}</td>
                        <td class="px-4 py-3 text-gray-600">
                            {{ $course->lecturer?->name ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @php
                                $badge = match($course->status) {
                                    'active'   => 'bg-green-100 text-green-700',
                                    'archived' => 'bg-gray-100 text-gray-500',
                                    default    => 'bg-yellow-100 text-yellow-700',
                                };
                                $label = match($course->status) {
                                    'active'   => 'Aktif',
                                    'archived' => 'Diarsipkan',
                                    default    => 'Draft',
                                };
                            @endphp
                            <span class="inline-block rounded-full px-2.5 py-0.5 text-xs font-medium {{ $badge }}">
                                {{ $label }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="inline-flex items-center gap-3">
                                <a href="{{ route('courses.edit', $course) }}"
                                   class="text-sm font-medium text-slate-600 hover:text-slate-900 hover:underline">
                                    Edit
                                </a>
                                <button type="button"
                                        onclick="bukaModalHapus('{{ route('courses.destroy', $course) }}', '{{ addslashes($course->name) }}')"
                                        class="text-sm font-medium text-red-600 hover:text-red-800 hover:underline">
                                    Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-gray-400">
                            Belum ada data mata kuliah.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if ($courses->hasPages())
        <div class="mt-5">
            {{ $courses->links() }}
        </div>
    @endif

    {{-- Modal Konfirmasi Hapus --}}
    <div id="modal-hapus"
         class="fixed inset-0 z-50 hidden items-center justify-center"
         role="dialog" aria-modal="true" aria-labelledby="modal-judul">

        {{-- Overlay gelap --}}
        <div class="absolute inset-0 bg-black/50" onclick="tutupModalHapus()"></div>

        {{-- Panel modal --}}
        <div class="relative mx-4 w-full max-w-sm rounded-xl bg-white p-6 shadow-xl">
            <div class="mb-4 flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-100">
                    <svg class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                </div>
                <h3 id="modal-judul" class="text-base font-semibold text-gray-900">Konfirmasi Penghapusan</h3>
            </div>
            <p class="mb-6 text-sm text-gray-600">
                Apakah Anda yakin ingin menghapus mata kuliah
                <strong id="modal-nama" class="text-gray-900"></strong>?
                Tindakan ini tidak dapat dibatalkan.
            </p>
            <div class="flex justify-end gap-3">
                <button type="button"
                        onclick="tutupModalHapus()"
                        class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Batal
                </button>
                <form id="form-hapus" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
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
        // Tutup modal dengan tombol Escape
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') tutupModalHapus();
        });
    </script>

</x-layout>
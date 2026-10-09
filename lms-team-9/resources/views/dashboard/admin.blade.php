<x-layout title="Dashboard Admin">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Dashboard Admin</h1>
        <p class="text-sm text-gray-600">Selamat datang kembali, {{ auth()->user()->name }}!</p>
    </div>

    {{-- Ringkasan Statistik --}}
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-4 mb-8">
        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <span class="text-xs font-semibold uppercase tracking-wider text-gray-400">Total Pengguna</span>
            <p class="mt-2 text-3xl font-bold text-gray-900">{{ $stats['total_users'] }}</p>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <span class="text-xs font-semibold uppercase tracking-wider text-gray-400">Mata Kuliah</span>
            <p class="mt-2 text-3xl font-bold text-gray-900">{{ $stats['total_courses'] }}</p>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <span class="text-xs font-semibold uppercase tracking-wider text-gray-400">Total Dosen</span>
            <p class="mt-2 text-3xl font-bold text-slate-700">{{ $stats['total_dosen'] }}</p>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <span class="text-xs font-semibold uppercase tracking-wider text-gray-400">Total Mahasiswa</span>
            <p class="mt-2 text-3xl font-bold text-slate-700">{{ $stats['total_mhs'] }}</p>
        </div>
    </div>

    {{-- Aksi Cepat --}}
    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="mb-4 text-lg font-semibold text-gray-900">Aksi Cepat Admin</h2>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.users.index') }}" 
               class="rounded-md bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
                Kelola Pengguna
            </a>
            <a href="{{ route('admin.users.create') }}" 
               class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                + Tambah Akun
            </a>
            <a href="{{ route('courses.index') }}" 
               class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Kelola Mata Kuliah
            </a>
        </div>
    </div>
</x-layout>
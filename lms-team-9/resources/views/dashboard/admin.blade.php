<x-layout title="Dashboard Admin">
    <x-page-header title="Dashboard Admin" subtitle="Ringkasan pengelolaan KampusLMS." />
    <div class="stats-grid">
        <x-card>
            <p class="stat-label">Total pengguna</p>
            <p class="stat-value">{{ $stats['total_users'] }}</p>
        </x-card>
        <x-card>
            <p class="stat-label">Mata kuliah</p>
            <p class="stat-value">{{ $stats['total_courses'] }}</p>
        </x-card>
        <x-card>
            <p class="stat-label">Mahasiswa</p>
            <p class="stat-value">{{ $stats['total_mhs'] }}</p>
            <p class="field-hint">Jumlah seluruh akun mahasiswa.</p>
        </x-card>
        <x-card>
            <p class="stat-label">Dosen</p>
            <p class="stat-value">{{ $stats['total_dosen'] }}</p>
        </x-card>
    </div>
    <x-card title="Aksi cepat">
        <div class="cluster">
            <a class="button button--primary" href="{{ route('admin.users.index') }}">Kelola pengguna</a>
            <a class="button button--secondary" href="{{ route('admin.users.create') }}">Tambah pengguna</a>
            <a class="button button--secondary" href="{{ route('courses.index') }}">Kelola mata kuliah</a>
        </div>
    </x-card>
</x-layout>

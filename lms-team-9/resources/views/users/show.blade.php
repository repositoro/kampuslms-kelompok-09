<x-layout title="Detail Pengguna">
    <x-breadcrumb><a href="{{ route('admin.users.index') }}">Pengguna</a><span>Detail akun</span></x-breadcrumb>
    <x-page-header title="{{ $user->name }}" subtitle="Informasi akun pengguna.">
        <x-slot:actions>
            <a class="button button--secondary" href="{{ route('admin.users.edit', $user) }}">Ubah pengguna</a>
        </x-slot:actions>
    </x-page-header>
    <x-card title="Informasi akun">
        <dl class="details-grid">
            <dt>Nama</dt><dd>{{ $user->name }}</dd>
            <dt>Email</dt><dd>{{ $user->email }}</dd>
            <dt>NIM/NIP</dt><dd>{{ $user->nim_nip ?? '—' }}</dd>
            <dt>Peran</dt><dd><x-badge variant="info">{{ ucfirst($user->role) }}</x-badge></dd>
            <dt>Terdaftar</dt><dd>{{ $user->created_at->format('d M Y, H:i') }}</dd>
        </dl>
        <a href="{{ route('admin.users.index') }}">Kembali ke daftar pengguna</a>
    </x-card>
</x-layout>

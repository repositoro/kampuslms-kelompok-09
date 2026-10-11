<x-layout title="403 — Akses Ditolak">
    <section class="error-page">
        <p class="error-code">403</p>
        <h1>Akses ditolak</h1>
        <p class="muted">Maaf, Anda tidak memiliki izin untuk mengakses halaman ini.</p>
        @auth
            <a class="button button--primary" href="{{ route('dashboard') }}">Kembali ke dashboard</a>
        @else
            <a class="button button--primary" href="{{ route('login') }}">Masuk</a>
        @endauth
    </section>
</x-layout>

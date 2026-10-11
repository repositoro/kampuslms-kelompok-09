<x-layout title="404 — Halaman Tidak Ditemukan">
    <section class="error-page">
        <p class="error-code">404</p>
        <h1>Halaman tidak ditemukan</h1>
        <p class="muted">Halaman atau materi yang Anda cari tidak tersedia.</p>
        @auth
            <a class="button button--primary" href="{{ route('dashboard') }}">Kembali ke dashboard</a>
        @else
            <a class="button button--primary" href="{{ route('login') }}">Masuk</a>
        @endauth
    </section>
</x-layout>

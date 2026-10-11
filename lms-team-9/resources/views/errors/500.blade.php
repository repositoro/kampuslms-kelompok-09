<x-layout title="500 — Terjadi Kesalahan">
    <section class="error-page">
        <p class="error-code">500</p>
        <h1>Terjadi kesalahan</h1>
        <p class="muted">Permintaan belum dapat diproses. Silakan coba kembali nanti.</p>
        @auth
            <a class="button button--primary" href="{{ route('dashboard') }}">Kembali ke dashboard</a>
        @else
            <a class="button button--primary" href="{{ route('login') }}">Kembali ke beranda</a>
        @endauth
    </section>
</x-layout>

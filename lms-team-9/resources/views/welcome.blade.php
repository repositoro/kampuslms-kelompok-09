<x-guest-layout title="KampusLMS — Pembelajaran dalam satu ruang">
    <section class="card login-card">
        <p class="eyebrow">Sistem manajemen pembelajaran</p>
        <h1>Selamat datang di KampusLMS</h1>
        <p>Ruang belajar digital untuk mengelola mata kuliah, materi, dan tugas.</p>
        @auth
            <a class="button button--primary" href="{{ route('dashboard') }}">Buka dashboard</a>
        @else
            <a class="button button--primary" href="{{ route('login') }}">Masuk ke akun</a>
        @endauth
    </section>
</x-guest-layout>

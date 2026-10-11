<x-guest-layout title="KampusLMS — Pembelajaran dalam satu ruang">
    <section class="card landing-panel">
        <p class="eyebrow">Sistem Pembelajaran Perkuliahan</p>
        <h1>Belajar lebih terarah bersama KampusLMS</h1>
        <p>Ruang belajar digital untuk mengelola mata kuliah, materi, pengumpulan tugas, dan nilai dalam satu tempat.</p>
        @auth
            <a class="button button--primary" href="{{ route('dashboard') }}">Buka dashboard</a>
        @else
            <a class="button button--primary" href="{{ route('login') }}">Masuk ke akun</a>
        @endauth
    </section>
    <footer><span>Sederhana untuk dipakai. Jelas untuk dibangun.</span></footer>
</x-guest-layout>

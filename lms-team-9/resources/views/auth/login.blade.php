<x-guest-layout title="Masuk — KampusLMS">
    <section class="card login-card">
        <h1>Masuk ke KampusLMS</h1>
        <p>Gunakan akun terdaftar untuk melanjutkan pembelajaran.</p>
        @if (session('status'))
            <x-alert variant="success">{{ session('status') }}</x-alert>
        @endif
        <form action="{{ route('login') }}" method="POST" class="form-stack">
            @csrf
            <x-form-field name="email" label="Alamat email" :required="true">
                <x-input id="email" name="email" type="email" autocomplete="email" required
                    value="{{ old('email') }}" placeholder="nama@kampuslms.test" />
            </x-form-field>
            <x-form-field name="password" label="Kata sandi" :required="true">
                <x-input id="password" name="password" type="password" autocomplete="current-password" required
                    placeholder="Masukkan kata sandi" />
            </x-form-field>
            <div class="login-options">
                <span class="muted">Akses aman untuk sivitas kampus</span>
            </div>
            <x-button class="button--wide">Masuk</x-button>
        </form>
    </section>
</x-guest-layout>

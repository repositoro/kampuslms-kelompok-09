<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Masuk — KampusLMS' }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="guest-page">
    <main class="guest-content">
        <a class="brand guest-brand" href="{{ url('/') }}">
            <span class="brand-mark" aria-hidden="true">K</span>
            <span>Kampus<span class="brand-accent">LMS</span></span>
        </a>
        @if ($errors->any())
            <x-alert variant="error">Periksa kembali email dan kata sandi Anda.</x-alert>
        @endif
        @if (session('success'))
            <x-alert variant="success">{{ session('success') }}</x-alert>
        @endif
        {{ $slot }}
    </main>
</body>
</html>

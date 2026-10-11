<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Masuk — KampusLMS' }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="guest-page">
    <div class="guest-shell">
        <main class="guest-content">
            <x-logo class="guest-brand" :href="url('/')" />
            @if ($errors->any())
                <x-alert variant="error">Periksa kembali email dan kata sandi Anda.</x-alert>
            @endif
            @if (session('success'))
                <x-alert variant="success">{{ session('success') }}</x-alert>
            @endif
            {{ $slot }}
        </main>
    </div>
</body>
</html>

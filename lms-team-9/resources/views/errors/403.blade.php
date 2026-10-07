<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Akses Ditolak</title>
</head>
<body style="font-family: sans-serif; text-align: center; padding: 50px;">
    <h1>403</h1>
    <h2>Akses Ditolak</h2>
    <p>Maaf, Anda tidak memiliki izin untuk mengakses halaman ini.</p>
    <a href="{{ url()->previous() }}">Kembali ke halaman sebelumnya</a> atau
    <a href="{{ route('dashboard') }}">kembali ke dashboard</a>.
</body>
</html>

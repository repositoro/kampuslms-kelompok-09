### READ — Telusuri satu request penuh (30 menit)

Ambil route `/tentang` yang Anda buat minggu lalu. Tanpa AI, tulis di catatan Anda:

1. Baris mana di `routes/web.php` yang menangkapnya?
2. Kalau ditangani controller, berkas dan method mana?
3. View mana yang dikembalikan? Di path apa persisnya?
4. Layout apa yang membungkusnya?
5. Jalankan `php artisan route:list --path=tentang`. Cocok dengan analisis Anda?

**Jawab**
1. pada baris ini [Route::get('/tentang', function () { return view('tentang'); });]
2. tidak menggunakan controller, karena pada bagian function () { return view('tentang'); });]ini disebut closure yang artinya logic ditulis langsung di dalam routes bukan di panggil dari file controller terpisah.
3. file yang ditampilkan view ('tentang') mengarah ke file resources/views/tentang.blade.php
4. Pada halaman /tentang tidak menggunakan layout apapun. file tentang.blade.php adalah dokumen HTML lengkap yang berdiri sendiri, menampilkan nama project dan daftar 4 anggota tim beserta nim maisng-masing individu.
5. ![alt text](image-4.png)
Hasil dari `php artisan route:list --path=tentang`menunjukkan hanya ada satu route yang sesuai, yaitu GET|HEAD tentang, berlokasi di routes/web.php:9. kolom nama route kosong karena memang tidak diberi nama dikode.
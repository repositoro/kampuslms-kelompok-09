## Soal ##

1. Gambar ulang ERD dari spesifikasi di papan/kertas, tanpa melihat dokumen.
2. Untuk setiap foreign key, tentukan perilaku onDelete-nya dan tuliskan alasannya.
3. Jawab: kalau seorang dosen dihapus, apa yang terjadi pada mata kuliahnya? Kenapa dirancang begitu?
4. Jawab: kenapa grades.submission_id bersifat unique, bukan sekadar index biasa?

## Jawab ##

1. 
```
Route::view('/tentang', 'tentang')->name('about');
```
ini adalah baris yang menangkap /tentang. Method-nya Route::view(), bukan Route::get() biasa. Ini shortcut khusus laravel untuk route yang hanya menampilkan view tanpa logic apapun.

2. Untuk route /tentang, tidak ada controller custom yang menanggapi request ini. Baris Route::view('/tentang', 'tentang')->name('about') menggunakan method Route::view(), yaitu shortcut khusus Laravel dirancang untuk kasus di mana sebuah halaman murni menampilkan konten statis tanpa perlu logika apapun (tidak ada query database, tidak ada pengolahan input, tidak ada kondisi). Karena tidak butuh logika,Laravel tidak mewajibkan membuat controller sendiri untuk ini.

3. 
View: tentang

Path fisik: resources/views/tentang.blade.php

(Nama 'tentang' di Route::view('tentang', 'tentang") itu dot-notation Blade otomatis dicari di resource/viws/tentang.Blade.php.)

4. 
Isi file tentang.blade.php seluruh konten halaman dibungkus oleh komponen <x-layout title="Tentang"> ... </x-layout>.
```
<x-layout title="Tentang">
    ...
</x-layout>
```

Ini artinya halaman /tentang tidak berdiri sendiri sebagai HTML utuh, melainkan bergantung pada kerangka HTML yang didefinisikan terpisah di resources/views/components/layout.blade.php file itulah yang menyediakan struktur <html>, <head>, <title>, dan elemen navigasi yang digunakan bersama di seluruh halaman.

Dibungkus komponen <x-layout>, yang fisiknya ada di resources/views/components/layout.blade.php.

5. php artisan route:list --path=tentang

Hasilnya akan seperti ini pada terminal
```
GET|HEAD    tentang ... about
Showing [1] routes
```
command route:list mengonfirmasi bahwa hanya ada satu jalur yang menangkap /tentang, tidak ada route lain yang bentrok atau menutupi. Aman karena hanya satu route dengan URI itu, jadi tidak ada risiko salah menangkap.

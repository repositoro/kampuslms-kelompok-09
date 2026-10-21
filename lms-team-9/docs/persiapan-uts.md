# Catatan Belajar dan Persiapan UTS KampusLMS

Catatan ini disusun dari soal UTS dan implementasi yang ada di repo. Gunakan sebagai peta belajar, bukan naskah hafalan: siapkan contoh kode yang memang pernah kamu kerjakan dan jelaskan alasan serta akibat desainnya.

## Bentuk Ujian

- **Demo, 7 menit:** admin membuat mata kuliah, menetapkan dosen, mendaftarkan mahasiswa; dosen membuat materi dan tugas; mahasiswa melihat mata kuliah yang diikuti.
- **Code walkthrough, 8 menit:** 2–3 fitur dipilih acak. Setiap anggota menjelaskan kode atas namanya, keputusan desain, alternatif, dan dampak jika mekanismenya dihapus.
- **Uji keamanan, 5 menit:** penguji memilih titik rawan dari [`keamanan.md`](keamanan.md) dan mencoba akses lintas pengguna.
- Sebelum demo penguji menjalankan `php artisan migrate:fresh --seed`. Seeder saat ini membuat akun demo, 5 mata kuliah, tugas, dan data mahasiswa/submission; materi dibuat saat demo.
- Fitur yang belum berjalan disampaikan terus terang di awal. Nilai pemahaman individu dinilai terpisah.

## Persiapan Sebelum Ujian

1. Jalankan dari folder `lms-team-9`:

   ```powershell
   php artisan migrate:fresh --seed
   php artisan test
   ```

   Konteks persiapan saat catatan ini dibuat menunjukkan `migrate:fresh --seed` berhasil. Jalankan kembali menjelang ujian untuk memastikan kondisi terbaru.

2. Login lokal memakai akun di README. Password default seeder adalah `password`, kecuali konfigurasi lokal mengatur `DEMO_ACCOUNT_PASSWORD` berbeda. Jangan mengandalkan password ini di production.

   | Peran | Email demo |
   |---|---|
   | Admin | `admin@kampuslms.test` |
   | Dosen | `dosen@kampuslms.test` |
   | Mahasiswa | `mahasiswa@kampuslms.test` |

3. Latih demo dengan mata kuliah baru dan kode yang belum dipakai. Seeder sudah membuat data; jangan melewati langkah yang diminta hanya karena data contoh sudah tersedia.
4. Buka `git log`/Source Control sebelum ujian. Catat commit dan file yang menjadi kontribusimu; jangan mengaku menulis kode anggota lain. CLI Git tidak tersedia pada terminal persiapan ini, jadi gunakan instalasi Git pada mesinmu atau panel Source Control.
5. Setiap anggota isi tabel persiapan di bawah dan berlatih menjelaskan satu fitur tanpa membaca naskah.
6. Baca [`api.md`](api.md), [`keamanan.md`](keamanan.md), test alur end-to-end, serta kode fitur yang menjadi tanggung jawabmu.

### Peta Kontribusi Anggota

| Anggota | Commit/file yang ditulis | Fitur yang bisa dijelaskan | Alternatif dan konsekuensi |
|---|---|---|---|
| [Nama] | [Commit dan file] | [Fitur] | [Alasan] |
| [Nama] | [Commit dan file] | [Fitur] | [Alasan] |
| [Nama] | [Commit dan file] | [Fitur] | [Alasan] |
| [Nama] | [Commit dan file] | [Fitur] | [Alasan] |

## Alur Demo 7 Menit

Pastikan aplikasi dan database siap sebelum waktu demo. Bergantian peran dengan transisi login/logout yang ringkas; jangan mengubah database langsung untuk menggantikan langkah UI.

1. **Admin, sekitar 2 menit:** login sebagai admin, buat mata kuliah dengan kode unik, pilih dosen pengampu, lalu daftarkan mahasiswa demo. Tunjukkan daftar/detail mata kuliah atau peserta sebagai bukti tersimpan.
2. **Dosen, sekitar 2 menit:** logout admin, login sebagai dosen yang ditetapkan, buka mata kuliah itu, buat satu materi dan satu tugas. Publikasikan tugas jika ada pilihan status agar terlihat oleh mahasiswa.
3. **Mahasiswa, sekitar 2 menit:** logout dosen, login sebagai mahasiswa yang didaftarkan, buka dashboard/daftar mata kuliah dan tunjukkan mata kuliah tadi beserta materi/tugas yang bisa diakses.
4. **Sisa waktu, sekitar 1 menit:** simpulkan alur data dan tunjukkan test [`EndToEndDemoWorkflowTest.php`](../tests/Feature/EndToEndDemoWorkflowTest.php) bila penguji meminta bukti uji.

Uji versi demo sendiri setelah `migrate:fresh --seed`. Hindari bergantung pada state database yang dibuat sebelum reset. Jika suatu langkah tidak berfungsi, sampaikan di awal dan tunjukkan bagian yang masih berjalan.

## Peta Kode Cepat

| Topik | Lokasi utama |
|---|---|
| Route web dan role | [`routes/web.php`](../routes/web.php) |
| Route API v1 dan Sanctum | [`routes/api.php`](../routes/api.php) |
| Alias middleware Laravel 12 | [`bootstrap/app.php`](../bootstrap/app.php) |
| Pengecekan role route | [`EnsureUserHasRole.php`](../app/Http/Middleware/EnsureUserHasRole.php) |
| Login web dan regenerasi sesi | [`LoginController.php`](../app/Http/Controllers/Auth/LoginController.php) |
| Mass assignment pada User | [`User.php`](../app/Models/User.php) |
| Pendaftaran mata kuliah | [`create_course_user_table.php`](../database/migrations/2026_09_15_102838_create_course_user_table.php), [`EnrollmentController.php`](../app/Http/Controllers/EnrollmentController.php) |
| Validasi input pengguna | [`StoreUserRequest.php`](../app/Http/Requests/StoreUserRequest.php), [`UserController.php`](../app/Http/Controllers/UserController.php) |
| Contoh otorisasi | [`CoursePolicy.php`](../app/Policies/CoursePolicy.php), [`SubmissionPolicy.php`](../app/Policies/SubmissionPolicy.php) |
| Seed akun demo | [`DemoAccountSeeder.php`](../database/seeders/DemoAccountSeeder.php), [`DatabaseSeeder.php`](../database/seeders/DatabaseSeeder.php) |
| Daftar dan filter pengguna | [`UserController.php`](../app/Http/Controllers/UserController.php), `resources/views/users/index.blade.php` |
| Matriks IDOR dan request uji | [`keamanan.md`](keamanan.md) |
| Test alur | [`EndToEndDemoWorkflowTest.php`](../tests/Feature/EndToEndDemoWorkflowTest.php) |

## Bank Soal Walkthrough

### 1. Migrasi dan constraint

Pilih migrasi yang benar-benar kamu pahami. Contoh pivot `course_user`:

- `foreignId('course_id')->constrained()` dan `foreignId('user_id')->constrained()` menghubungkan pendaftaran ke mata kuliah dan pengguna.
- `cascadeOnDelete()` menghapus baris pivot saat mata kuliah/pengguna dihapus, agar tidak ada relasi yatim.
- `unique(['course_id', 'user_id'])` memastikan satu mahasiswa hanya terdaftar satu kali pada satu mata kuliah.
- `enrolled_at` menyimpan waktu pendaftaran; timestamps mencatat waktu pembuatan/perubahan baris.

Constraint lain yang bisa ditunjukkan: email dan kode mata kuliah unik; `lecturer_id` memakai `restrictOnDelete()` agar dosen yang masih mengampu tidak bisa dihapus begitu saja; submission unik per pasangan tugas/mahasiswa; satu submission hanya punya satu grade. Jelaskan juga trade-off cascade: penghapusan induk dapat menghapus data turunannya.

### 2. Mengapa `course_user` punya unique composite?

Composite `(course_id, user_id)` mewakili keunikan hubungan, bukan keunikan salah satu kolom sendirian. Tanpanya, request berulang atau request bersamaan dapat membuat pendaftaran ganda, hitungan peserta salah, dan relasi duplikat. Pemeriksaan di controller saja tidak cukup untuk menjamin integritas pada request bersamaan; constraint database menjadi pertahanan terakhir. Alternatif seperti validasi aplikasi tetap berguna untuk pesan ramah, tetapi tidak menggantikan unique index.

### 3. Apa itu mass assignment dan pencegahannya?

Mass assignment adalah mengisi banyak atribut model sekaligus dari array request, misalnya `User::create($request->all())`. Jika semua input dipercaya, pengguna dapat menyisipkan atribut sensitif yang tidak dimaksudkan untuk form. Di `User`, `$fillable` hanya berisi `name`, `email`, `password`, dan `nim_nip`; `role` tidak diizinkan untuk pengisian massal. `UserController` mengambil `$request->validated()` lalu menetapkan `role` secara eksplisit setelah otorisasi.

### 4. Mengapa `role` tidak ada di `$fillable`, dan di mana nilainya diisi?

Role menentukan hak akses, sehingga tidak boleh berasal dari field yang bisa disisipkan sembarang pengguna lewat mass assignment. Saat admin membuat/mengubah pengguna, `UserController` menetapkan `$user->role = $data['role']` secara eksplisit; request memvalidasi role terhadap `admin`, `dosen`, atau `mahasiswa`. Seeder/factory juga memberi role melalui alur server-side tepercaya.

### 5. Tunjukkan Policy dan jelaskan

Contoh `CoursePolicy::view`: admin boleh melihat course; dosen hanya course dengan `lecturer_id` miliknya; mahasiswa hanya course yang punya relasi pendaftaran dengannya. Policy mengembalikan boolean keputusan akses. Controller memanggil `Gate::authorize(...)`; keputusan ini melindungi request, bukan sekadar UI. Alternatifnya, logika berulang ditulis di setiap controller, tetapi lebih mudah tidak konsisten dan lebih sulit diuji.

Contoh lain adalah `SubmissionPolicy::view`: dosen harus menjadi pengampu mata kuliah; mahasiswa harus pemilik submission dan masih lolos kondisi akses tugas; admin boleh melihat. Jangan hanya menyebut “policy mengecek role”; jelaskan juga hubungan pemilik/objeknya.

### 6. Mengapa `@can` di Blade tidak cukup?

`@can` menyembunyikan tombol/link dari pengguna yang tidak berhak, tetapi penyerang dapat melewati UI dan mengirim HTTP request langsung ke endpoint. Otorisasi server wajib diperiksa lagi oleh route middleware, controller/Gate, atau Form Request. Di aplikasi ini beberapa route admin memakai `role:admin`, dan controller memanggil `Gate::authorize`. Untuk membuktikan, kirim request langsung memakai akun berhak rendah; respons harus ditolak meskipun URL diketahui.

### 7. Perbedaan HTTP 401 dan 403

- **401 Unauthorized:** request belum terautentikasi, misalnya `GET /api/v1/me` tanpa Bearer token. Klien perlu login/autentikasi.
- **403 Forbidden:** pengguna sudah terautentikasi, tetapi role atau relasinya tidak memberi izin, misalnya dosen mencoba mengubah course lewat endpoint yang khusus admin, atau mahasiswa mencoba melihat submission mahasiswa lain.

Pada API, format error disesuaikan di `bootstrap/app.php`. Pastikan mengirim `Accept: application/json` agar format JSON digunakan.

### 8. Mengapa `$request->validated()` lebih aman daripada `$request->all()`?

`validated()` hanya mengembalikan field yang lolos rules Form Request/validator. `all()` dapat mencakup field tambahan yang tidak pernah dimaksudkan untuk diproses. Gunakan hasil validasi saat menyimpan, lalu tetap terapkan otorisasi dan mass-assignment protection; validasi bukan pengganti otorisasi.

### 9. Mengapa filter pencarian di query string, bukan session?

Filter daftar pengguna dikirim sebagai `?search=...&role=...`, sehingga URL bisa di-bookmark, dibagikan, di-refresh, dan tombol back/forward tetap masuk akal. Pagination memakai `withQueryString()` agar filter tetap berlaku di halaman berikutnya. Session lebih cocok untuk state sementara yang melekat pada pengguna, bukan kriteria tampilan yang sebaiknya tercermin di URL.

### 10. Mengapa session diregenerasi setelah login?

Setelah `Auth::attempt` berhasil, `$request->session()->regenerate()` mengganti session ID untuk mencegah session fixation: ID sesi sebelum autentikasi tidak terus dipakai sebagai ID sesi pengguna yang sudah login. Saat logout, sesi di-invalidate dan token CSRF diregenerasi.

### 11. Di mana middleware didaftarkan pada Laravel 12?

Alias middleware `role` didefinisikan di `bootstrap/app.php` melalui `$middleware->alias([...])`, lalu dipasang pada grup route seperti `role:admin` di `routes/web.php`. Pola Laravel 12 menggunakan konfigurasi bootstrap aplikasi; tutorial lama sering menunjukkan registrasi alias di `app/Http/Kernel.php`. Middleware role sendiri ada di `EnsureUserHasRole.php`.

### 12. Wajib: jelaskan penggunaan bantuan AI

Jawab jujur dan spesifik. Sebutkan bagian yang dibantu, apa yang kamu ubah setelah membaca/menguji hasilnya, dan alasan perubahan tersebut. Jangan mengklaim mengubah sesuatu bila tidak benar.

Kerangka jawaban:

> “Saya menggunakan AI untuk membantu [bagian konkret]. Saya memeriksa hasilnya terhadap [spesifikasi/test/kode sekitar], lalu mengubah [perubahan yang benar-benar kamu buat] karena [alasan desain/keamanan]. Saya menguji dengan [test atau demonstrasi], hasilnya [hasil]. Alternatifnya [alternatif], tetapi konsekuensinya [trade-off].”

## Uji Keamanan Langsung

1. Buka [`keamanan.md`](keamanan.md), pilih satu baris IDOR, dan jelaskan siapa penyerang, objek target, serta relasi yang seharusnya membatasi akses.
2. Pakai akun/token penyerang dan ID objek milik akun lain. Untuk API, login dahulu ke `POST /api/v1/auth/login`, lalu kirim `Authorization: Bearer <token>` dan `Accept: application/json`.
3. Di Windows PowerShell, panggil `curl.exe` (bukan alias `curl`) bila mengikuti contoh curl. Contoh request tanpa token yang seharusnya menghasilkan 401:

   ```powershell
   $API = 'http://127.0.0.1:8000/api/v1'
   curl.exe -i "$API/me" -H 'Accept: application/json'
   ```

4. Untuk contoh 403, gunakan skenario Course pada `keamanan.md`: token dosen mengirim `PUT /courses/5`, padahal update/delete course hanya admin. Sertakan `Authorization: Bearer <token-dosen>`, `Accept: application/json`, dan body JSON. Gunakan data seed lokal; request yang ditolak seharusnya tidak mengubah data.
5. Tunjukkan pertahanan di controller/Gate dan Policy. Jelaskan bahwa mengubah ID URL ke ID milik orang lain tidak boleh membuka akses.

Untuk submission, uji dengan submission milik mahasiswa lain. Untuk materi/tugas, pilih objek di mata kuliah dosen lain. Pastikan token yang dipakai memang berbeda pemilik/pengampu dengan objek target; kalau tidak, uji tidak membuktikan IDOR tertahan.

## Checklist Jawaban yang Meyakinkan

Saat menjawab fitur, susun penjelasan dengan urutan:

1. **Tujuan:** masalah apa yang diselesaikan?
2. **Lokasi:** file/method/constraint mana yang mengambil keputusan?
3. **Alasan:** mengapa desain ini dipilih dan alternatif apa yang ada?
4. **Konsekuensi:** bug/risiko apa yang muncul bila bagian ini dihapus?
5. **Bukti:** test, hasil HTTP, atau langkah demo apa yang menunjukkan perilakunya?

Sebelum selesai, pastikan setiap anggota dapat menunjukkan kontribusi sendiri, menjawab pertanyaan 12 dengan transparan, dan menjelaskan satu mekanisme keamanan dari request masuk sampai keputusan akses.
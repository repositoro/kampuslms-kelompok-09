# Laporan Checkpoint Minggu 6 - API KampusLMS

Berikut adalah laporan pengerjaan Modul API (READ + BUILD) untuk minggu ke-6.

## JAWABAN KUIS 6 (Evaluasi Teori)

### 1. Kenapa mengembalikan model mentah berbahaya? Peragakan kebocorannya.
Mengembalikan model mentah langsung dari objek Eloquent sangat berbahaya karena berpotensi mengekspos **semua atribut** (kolom database) yang ada di tabel. Meskipun Laravel menyediakan properti `$hidden` (untuk `password` dsb), model mentah tetap akan membocorkan data internal atau metadata sistem (seperti `role`, status verifikasi, relasi internal, dan `created_at`) yang seharusnya tidak perlu dilihat atau dimanipulasi klien. Jika developer lupa mendaftarkan kolom sensitif ke `$hidden`, maka data seperti API Key atau password hash akan langsung bocor.

**Langkah Memperagakan Kebocoran:**

1. Buka file controller otentikasi di `lms-team-9/app/Http/Controllers/Api/V1/AuthController.php`.
2. Cari method `me(Request $request)`.
3. Ubah kode di dalamnya dari yang tadinya rapi (menggunakan array/Resource) menjadi mengembalikan objek secara mentah-mentah seperti ini:

```php
    public function me(Request $request)
    {
        // CONTOH KODE BURUK: Langsung me-return model mentah
        return response()->json($request->user());
    }
```

4. Simpan file tersebut. Jalankan server Laravel jika belum (`php artisan serve`).
5. Dapatkan token Bearer Anda dengan login (simpan tokennya), lalu jalankan perintah `curl` ini di terminal baru:

```bash
curl -X GET http://localhost:8000/api/v1/auth/me \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <MASUKKAN_TOKEN_ANDA_DISINI>"
```

6. **Output yang dihasilkan di terminal:**

```json
{
  "id": 1,
  "name": "Admin Kampus",
  "email": "admin@example.com",
  "email_verified_at": null,
  "role": "admin",
  "created_at": "2026-10-07T13:47:12.000000Z",
  "updated_at": "2026-10-07T13:47:12.000000Z"
}
```
*(Seperti yang Anda lihat, sistem secara otomatis membongkar seluruh tabel `users` (kecuali yang di-$hidden), termasuk struktur field internal yang mungkin akan dimanfaatkan hacker untuk menebak kerentanan/role).*

**(Solusi Terapan di Kode Kita):** Itulah sebabnya pada implementasi akhir, kita membungkusnya secara eksplisit dalam API Resource seperti `return new UserResource($request->user());` untuk secara mutlak mengunci field mana saja yang boleh keluar (sehingga field internal aman tersembunyi).

### 2. Apa beda 401 dan 403? Tunjukkan di API Anda satu contoh masing-masing.
- **401 Unauthorized**: Berarti klien **belum terautentikasi** (belum login, token tidak ada, atau token tidak valid). Klien tidak dikenali oleh sistem.
  - **Contoh di API**: Memanggil endpoint `GET /api/v1/courses` *tanpa* menyertakan header `Authorization: Bearer <token>`.
- **403 Forbidden**: Berarti klien **sudah terautentikasi** (sistem tahu siapa Anda), tetapi Anda **tidak memiliki izin (hak akses)** untuk melakukan aksi tersebut.
  - **Contoh di API**: Akun dengan role `mahasiswa` memanggil `POST /api/v1/courses` untuk membuat kelas, maka akan ditolak (403) karena hanya dosen/admin yang boleh. Atau Dosen A mencoba mengedit `PUT /api/v1/courses/2` yang merupakan milik Dosen B.

### 3. Kenapa `routes/api.php` tidak ada secara default di Laravel 11/12? Bagaimana mengaktifkannya?
Mulai dari Laravel 11, struktur aplikasi dirancang agar seringkas mungkin. Tidak semua aplikasi web membutuhkan API (sebagian hanya monolitik menggunakan Blade). Oleh karena itu, file routing API tidak disertakan secara default agar _boilerplate_ lebih bersih.

**Cara mengaktifkannya:**
Menjalankan perintah terminal:
```bash
php artisan install:api
```
Perintah ini akan secara otomatis:
1. Membuat file `routes/api.php`.
2. Mendaftarkan file tersebut ke dalam routing di `bootstrap/app.php` dengan menambahkan `api: __DIR__.'/../routes/api.php'`.
3. Menginstal Laravel Sanctum dan mempublikasikan file migrasi `personal_access_tokens`.

### 4. Apa fungsi `whenLoaded()`? Apa yang terjadi tanpanya?
Fungsi `whenLoaded('nama_relasi')` di dalam kelas API Resource digunakan untuk menyertakan data relasi **hanya jika relasi tersebut sudah di-load (eager loaded)** dari controller sebelumnya. BIAR MENCEGAH QUERY PROBLEM, QUERY NYA DAPAT MEMICU MASALAH N + 1

- **Jika tanpa `whenLoaded()`**: Misal kita menggunakan `$this->lecturer` di dalam `CourseResource`. Apabila controller hanya memanggil `Course::paginate()` (tanpa `with('lecturer')`), maka Laravel akan melakukan **lazy loading**. Ini memicu masalah N+1 Query (query dijalankan berulang kali di dalam loop untuk setiap baris data Course), yang membuat performa API menjadi sangat lambat.

### 5. Kenapa pesan gagal login tidak boleh membedakan email salah dan password salah?
Untuk mencegah celah keamanan yang disebut **User Enumeration** (Eksploitasi Pendaftaran Email).
Jika sistem membedakan pesan (misal: "Email tidak terdaftar" vs "Password salah"), peretas (hacker) dapat memasukkan ribuan email acak ke halaman login. Setiap kali mereka mendapat respons "Password salah", mereka jadi tahu dengan pasti bahwa email tersebut **valid/terdaftar** di sistem, dan bisa menargetkan email tersebut untuk serangan _brute-force_ password. Dengan menyamakan pesan (misal: *"Kredensial tidak valid"*), peretas tidak akan tahu apakah emailnya yang salah atau passwordnya yang salah.

### 6. Kenapa endpoint login wajib di-throttle? Berapa nilai yang Anda pakai dan mengapa?
Endpoint login adalah sasaran utama serangan *Brute-Force* (mencoba jutaan kombinasi password) dan *Credential Stuffing* (memasukkan password bocor dari situs lain). Throttling membatasi jumlah tebakan per menit untuk melumpuhkan serangan otomatis tersebut. 

**Nilai yang dipakai:**
Di `AppServiceProvider::boot()`, saya menggunakan:
```php
RateLimiter::for('login', function (Request $request) {
    return Limit::perMinute(5)->by($request->input('email').'|'.$request->ip());
});
```
**Alasan**: Limit `5 request per menit` merupakan batasan yang sangat wajar bagi manusia. Jika pengguna asli salah ketik password, mereka punya 5 kali kesempatan setiap menit. Namun bagi bot/hacker yang mencoba ribuan password per detik, kecepatan ini akan langsung menggagalkan serangan mereka. Rate limit diikat ke gabungan `email` dan `IP` (`email|IP`) agar bot tidak bisa gonta-ganti IP secara dinamis untuk menyerang satu email bertubi-tubi.

---

## LAPORAN PRAKTIKUM MINGGU 6

### 1. File Dibuat / Diubah
- **Baru**: `routes/api.php`, `docs/read-week6.md`, `docs/api.md`, `docs/jalur-frontend.md`, `scripts/test-api.sh`, `make_api.php`.
- **Baru (Controllers)**: `AuthController`, `CourseController`, `MaterialController`, `AssignmentController`, `SubmissionController`, `GradeController` (Semuanya di dalam folder `Api/V1`).
- **Baru (Resources)**: `UserResource`, `CourseResource`, `MaterialResource`, `AssignmentResource`, `SubmissionResource`, `GradeResource`.
- **Baru (Requests)**: 9 File FormRequest untuk validasi API.
- **Ubah**: `bootstrap/app.php` (menambahkan custom Exception JSON), `AppServiceProvider.php` (Rate Limiting & Prevent Lazy Loading), `User.php` (trait HasApiTokens), `routes/web.php` & views terkait (Memperbaiki konflik nama route `users.index`).

### 2. Implementasi Seluruh Endpoint (Sesuai Syarat)
- Telah mengaktifkan Laravel Sanctum dan memfungsikan login Bearer token (`install:api`).
- Seluruh endpoint CRUD (Course, Material, Assignment, Submission, Grade) sudah dibangun dan dilindungi dengan middleware `auth:sanctum`.
- Validasi data sudah terpisah dengan rapi menggunakan `FormRequest`.
- Struktur response untuk kondisi gagal validasi (422), Unauthorized (401), Forbidden (403), dan Not Found (404) telah diseragamkan lewat konfigurasi `->withExceptions()` di `bootstrap/app.php`.

### 3. Perlindungan IDOR dan Keamanan (Otorisasi)
Hak akses setiap endpoint telah dibatasi via fungsi `abort_unless`:
- **Mahasiswa** hanya dapat mengumpulkan tugas (Submission) miliknya sendiri dan mengakses Course.
- **Dosen** dilarang mengubah/menghapus/menilai mata kuliah dan tugas yang **bukan miliknya**. (Pemfilteran melalui id dosen pemilik).
- **Admin** diizinkan mengakses/memodifikasi tingkat global.

### 4. Hasil Verifikasi Sistem
1. Skrip `test-api.sh` menembak endpoint login 6 kali secara cepat dan berhasil mendapatkan balasan **429 Too Many Attempts** (throttle login).
2. Modus perlindungan eager-loading (`preventLazyLoading`) tidak men-trigger error saat endpoint mereturn `Resource::collection(...)`, membuktikan kueri relasi telah digabungkan dengan `with()` di controller.
3. Hak akses diuji coba silang antar user dengan keberhasilan menolak request (status 403 Forbidden) saat user berusaha mengakses resources milik user lain.

### 5. Asumsi & Sisa Pekerjaan
1. Otorisasi peran saat ini masih dikelola dengan _hardcoded function_ `abort_unless()` di level controller. Target minggu depan (minggu ke-7) adalah membersihkan controller ini dan mendesain infrastruktur kontrol otorisasi yang lebih tangguh melalui mekanisme **Laravel Policies/Gates**.
2. Berdasarkan perundingan untuk bagian UI (*B8 Jalur Frontend*), dokumentasi hasil rapat diletakkan di `docs/jalur-frontend.md` dan menanti ketetapan platform yang akan men-consume API Sanctum kita (contoh: Inertia.js atau React Native Mobile).

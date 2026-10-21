# Checkpoint 9 — Keamanan Penyimpanan & Unduhan Submission

Dokumen ini menjawab 6 pertanyaan audit keamanan berkas submission pada proyek `lms-team-9`.
Semua kutipan kode diambil dari repositori ini; path dan nomor baris disertakan.

---

### 1. Kenapa submission tidak boleh disimpan di `storage/app/public`? Peragakan kebocorannya.

Disk `public` pada [config/filesystems.php](config/filesystems.php:41) memiliki `root` di `storage/app/public`, dan ditautkan ke folder publik lewat `storage:link`:

```php
// config/filesystems.php:41
'public' => [
    'driver' => 'local',
    'root' => storage_path('app/public'),
    'url' => rtrim(env('APP_URL', 'http://localhost'), '/').'/storage',
    'visibility' => 'public',
    'throw' => false,
    'report' => false,
],

// config/filesystems.php:76
'links' => [
    public_path('storage') => storage_path('app/public'),
],
```

Setelah `php artisan storage:link`, isi `storage/app/public` muncul di `public/storage` sebagai symlink. Web server (Nginx/Apache) melayani `public/storage/...` sebagai **file statis langsung**, sehingga request itu **tidak pernah masuk ke `public/index.php`** dan tidak melewati middleware `auth` maupun `Gate`/policy Laravel. Siapa pun yang tahu URL-nya dapat mengunduh berkas tanpa login.

**Langkah peragaan kebocoran (skenario bila file tersimpan di disk `public`):**

1. Mahasiswa A login, buka halaman tugas, unggah berkas. Bila service menyimpan ke disk `public`, hasilnya `storage/app/public/submissions/{assignment_id}/{hash}.pdf`.
2. Salin URL yang terbentuk: `http://lms-team-9.test/storage/submissions/1/AbCdEf123....pdf`.
3. Buka jendela **incognito** (tanpa sesi login) dan akses URL tersebut. Karena symlink `public/storage` ada, berkas akan **terunduh tanpa autentikasi**.
4. Verifikasi lewat terminal:

```sh
# tanpa cookie/sesi sama sekali — kalau bocor, respons 200 + isi file
curl -i "http://lms-team-9.test/storage/submissions/1/AbCdEf123.pdf"

# bandingkan dengan endpoint yang benar (butuh sesi login, tanpa sesi -> 302 ke /login)
curl -i "http://lms-team-9.test/submissions/1/download"
```

**Yang benar-benar dipakai proyek saat ini:** service menyimpan ke disk `local`, bukan `public`.

```php
// app/Services/SubmissionService.php:20
$filePath = $file->store("submissions/{$assignment->id}", 'local');
```

```php
// config/filesystems.php:33
'local' => [
    'driver' => 'local',
    'root' => storage_path('app/private'),
    'serve' => true,
    'throw' => true,
    'report' => false,
],
```

Verifikasi nyata: `public/storage` **tidak ada** di working tree (`Test-Path public\storage` → `False`), sedangkan berkas ada di `storage/app/private/submissions/...`. Jadi **sudah aman**: berkas tidak dilayani sebagai statis dan hanya bisa diunduh lewat `SubmissionDownloadController` yang memanggil `Gate::authorize('view', $submission)`.

Catatan: `'serve' => true` pada disk `local` mendaftarkan route `storage/{path}` (lihat `php artisan route:list --path=storage`) untuk **URL bertanda tangan** (`signed URL`). Route ini menolak akses tanpa tanda tangan valid (403 di lokal / 404 di produksi) — lihat `vendor/laravel/framework/src/Illuminate/Filesystem/ServeFile.php:27-30`. Ini bukan jalur yang dipakai fitur unduh submission (kode memakai `Storage::disk('local')->download(...)` di controller ber-otorisasi), sehingga tetap aman.

---

### 2. Kenapa nama berkas fisik dibuat acak? Serangan apa yang dicegah?

Service memakai `$file->store($path, 'local')`. Secara internal Laravel memakai `hashName()`:

```php
// vendor/laravel/framework/src/Illuminate/Http/UploadedFile.php:34-37
public function store($path = '', $options = [])
{
    return $this->storeAs($path, $this->hashName(), $this->parseOptions($options));
}
```

```php
// vendor/laravel/framework/src/Illuminate/Http/FileHelpers.php:42-54
public function hashName($path = null)
{
    if ($path) {
        $path = rtrim($path, '/').'/';
    }
    $hash = $this->hashName ?: $this->hashName = Str::random(40);
    if ($extension = $this->guessExtension()) {
        $extension = '.'.$extension;
    }
    return $path.$hash.$extension;
}
```

Jadi nama fisik = string acak 40 karakter (bukan nama asli dari klien). Nama asli disimpan terpisah di kolom DB untuk ditampilkan/dipakai saat unduh:

```php
// app/Services/SubmissionService.php:25
'original_name' => basename($file->getClientOriginalName()),
```

```php
// app/Http/Controllers/SubmissionDownloadController.php:16
return Storage::disk('local')->download($submission->file_path, $submission->original_name);
```

```php
// resources/views/dosen/assignments/show.blade.php:52
<dd>{{ $submission->original_name }} &middot; {{ number_format($submission->file_size / 1024, 0) }} KB</dd>
```

Serangan/masalah yang dicegah:
- **Path traversal** (`../../.env`, `..\..\`): nama acak tidak mengandung `..`/separator, jadi tidak bisa keluar dari direktori tujuan.
- **Overwrite / tabrakan nama**: dua mahasiswa mengunggah `tugas.pdf` tidak akan saling menimpa karena nama fisik berbeda.
- **Enumerasi/penebakan URL**: URL tidak dapat diprediksi dari nama asli atau ID, sehingga IDOR tebak-tebakan lebih sulit.
- **Eksekusi file berbahaya**: ekstensi file fisik ditentukan `guessExtension()` (dari MIME), bukan dari nama klien, sehingga tidak bisa dipaksa `.php`; ekstensi ganda (`tugas.php.pdf`) juga dinetralkan.
- **Karakter berbahaya/NUL/unicode**: nama acak menghindari nama file berbahaya dari klien.

---

### 3. Apa beda validasi `mimes` dan `extensions`? Mana yang lebih aman dan kenapa?

- `mimes:...` memeriksa **isi** berkas lewat deteksi MIME (finfo/magic bytes). Ini memvalidasi konten sebenarnya.
- `extensions:...` hanya memeriksa **ekstensi pada nama berkas yang dikirim klien**. Klien dapat mengganti nama dengan bebas, jadi ini tidak membuktikan isi.

Yang lebih aman: **`mimes`**, karena menilai isi nyata. Batasannya:
- **Polyglot file**: berkas bisa valid sebagai dua format sekaligus (mis. PDF yang sekaligus berisi kode). `mimes` meloloskan yang terdeteksi sebagai PDF.
- Deteksi MIME bergantung pada file signature; format tertentu sulit dibedakan.

Karena itu perlu **pertahanan berlapis**: (1) `mimes` (isi), (2) `extensions` (nama), (3) `max` (ukuran), (4) nama fisik acak + ekstensi dari `guessExtension()`, (5) disimpan di disk **privat** dan hanya dilayani via controller ber-otorisasi dengan header unduhan.

Aturan validasi yang dipakai proyek:

```php
// app/Http/Requests/StoreSubmissionRequest.php:20
'file' => ['required', 'file', 'mimes:pdf,doc,docx,zip', 'extensions:pdf,doc,docx,zip', 'max:10240'],
```

Proyek memakai **keduanya** (`mimes` + `extensions`) — kombinasi yang lebih ketat daripada hanya `extensions`. Setara untuk material:

```php
// app/Http/Requests/StoreMaterialRequest.php:26-28
'mimes:pdf,pptx',
'extensions:pdf,pptx',
'max:10240',
```

---

### 4. `max:5120` berarti berapa MB? Apa yang bisa membatalkan batas ini di level server?

Catatan penting: aturan nyata di proyek ini **bukan** `max:5120`, melainkan `max:10240`.

```php
// app/Http/Requests/StoreSubmissionRequest.php:20
'file' => ['required', 'file', 'mimes:pdf,doc,docx,zip', 'extensions:pdf,doc,docx,zip', 'max:10240'],
```

Perhitungan untuk `max:5120` (satuan KB): `5120 KB ÷ 1024 = 5 MB`.
Perhitungan untuk nilai nyata proyek: `10240 KB ÷ 1024 = 10 MB`.

Pengaturan server yang bisa menolak **lebih dulu** sebelum aturan validasi Laravel berjalan:
- `upload_max_filesize` — batas ukuran satu berkas yang boleh diunggah PHP.
- `post_max_size` — batas total body request (harus ≥ `upload_max_filesize`).
- `max_file_uploads` — jumlah maksimal file per request (default 20).
- `client_max_body_size` (Nginx) — batas body di level web server.
- `LimitRequestBody` (Apache) — batas body di level Apache.
- Batas waktu: `max_input_time`, `max_execution_time`, serta `client_body_timeout`/`fastcgi_read_timeout`.

**Nilai yang terpasang di lingkungan lokal ini (Herd + PHP 8.4, `C:\Users\toroa\.config\herd\bin\php84\php.ini`):**

| Setting | Nilai |
|---|---|
| `upload_max_filesize` | 2M |
| `post_max_size` | 8M |
| `max_file_uploads` | 20 |
| `max_input_time` | 60 |
| `max_execution_time` | 30 |
| `client_max_body_size` (Nginx, `lms-team-9.test.conf`) | 128M |

**Analisis konsistensi:** aturan aplikasi `max:10240` (10 MB) **tidak konsisten** dengan PHP lokal. `upload_max_filesize = 2M` dan `post_max_size = 8M` membuat berkas > 2 MB gagal di PHP sebelum validasi Laravel berjalan; bahkan berkas hingga 10 MB yang seharusnya diterima akan ditolak server. Nginx `client_max_body_size 128M` cukup longgar, jadi bukan Nginx yang menjadi hambatan.

**Tidak dapat diverifikasi dari repositori:** konfigurasi PHP/Nginx server **produksi**. Yang diperiksa di atas adalah konfigurasi mesin pengembang (di luar repo). Cara memeriksa produksi:

```sh
php -i | findstr /I "upload_max_filesize post_max_size max_file_uploads"
# Nginx:  nginx -T | findstr /I client_max_body_size
# Apache: grep -R LimitRequestBody /etc/apache2/
```

---

### 5. Tunjukkan di kode Anda apa yang mencegah mahasiswa mengunduh submission mahasiswa lain.

Tiga lapis perlindungan yang saling melengkapi:

**(A) Autentikasi** — route unduhan berada dalam grup `auth` (wajib login) di [routes/web.php](routes/web.php:28):

```php
// routes/web.php:28
Route::middleware(['auth'])->group(function () {
    // ...
    // routes/web.php:33-34
    Route::get('/submissions/{submission}/download', SubmissionDownloadController::class)
        ->name('submissions.download');
```

Versi API dilindungi `auth:sanctum`:

```php
// routes/api.php:18
Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    // routes/api.php:34-35
    Route::get('/submissions/{submission}/download', SubmissionDownloadController::class)
        ->name('submissions.download');
```

Tanpa sesi/token yang valid: web → redirect ke `/login` (302); API → **401**.

**(B) Otorisasi kepemilikan** — controller memanggil policy:

```php
// app/Http/Controllers/SubmissionDownloadController.php:11-17
public function __invoke(Submission $submission)
{
    Gate::authorize('view', $submission);
    abort_unless(Storage::disk('local')->exists($submission->file_path), 404);

    return Storage::disk('local')->download($submission->file_path, $submission->original_name);
}
```

Policy mengecek bahwa mahasiswa hanya boleh melihat submission miliknya sendiri:

```php
// app/Policies/SubmissionPolicy.php:17-29
public function view(User $user, Submission $submission): bool
{
    if ($user->role === 'admin') {
        return true;
    }
    if ($user->role === 'dosen') {
        return $submission->assignment->course->lecturer_id === $user->id;
    }

    return $user->role === 'mahasiswa'
        && $submission->user_id === $user->id
        && $this->create($user, $submission->assignment);
}
```

Jika mahasiswa B meminta submission milik mahasiswa A, `$submission->user_id === $user->id` bernilai salah → `Gate::authorize` melempar `AuthorizationException` → **403** (API: dipetakan ke 403 di [bootstrap/app.php](bootstrap/app.php:40)). Tidak ada query "polos" yang mengambil berkas orang lain.

**(C) Penyimpanan di disk privat** — berkas disimpan di disk `local` (`storage/app/private`), bukan disk `public`, sehingga tidak ada jalur statis `public/storage` untuk menembus otorisasi. Lihat [config/filesystems.php](config/filesystems.php:33) dan [SubmissionService.php](app/Services/SubmissionService.php:20).

**Skenario uji:** mahasiswa B (terdaftar di mata kuliah, bukan pemilik) mengakses `GET /submissions/{id_milik_A}/download`.
- Tanpa login → **302** ke `/login`.
- Login sebagai B → **403** (berkas milik A tidak terunduh).
- Pemilik A mengakses URL yang sama → **200** + berkas terunduh.

Sudah diverifikasi oleh test yang ada:

```php
// tests/Feature/SubmissionWorkflowTest.php:148
$this->get("/submissions/{$submission->id}/download")->assertForbidden();
```

```php
// tests/Feature/ApiSubmissionAuthorizationTest.php:71
$this->getJson("/api/v1/submissions/{$submission->id}")->assertForbidden();
```

> Catatan: untuk submission yang **tidak ada**, `Gate::authorize` pada model yang sudah di-resolve route-model-binding akan memicu policy `view` dengan model `null` → Laravel melempar `404`, bukan 403. Jadi untuk ID tak dikenal hasilnya 404 (tidak membocorkan keberadaan data).

---

### 6. Kenapa `is_late` dihitung di server, bukan dikirim dari browser?

Input dari klien dapat dimanipulasi: mengubah jam perangkat, menyunting form lewat DevTools, atau mengirim request langsung dengan Postman/curl. Jika `is_late` diterima dari request, mahasiswa dapat menandai pengiriman terlambat sebagai tepat waktu (atau sebaliknya). Karena itu kebenaran waktu harus **dihitung di server** memakai jam server dan `due_at` dari database.

Perhitungan di server:

```php
// app/Services/SubmissionService.php:21-30
$submittedAt = now();
$submission->fill([
    'file_path' => $filePath,
    'original_name' => basename($file->getClientOriginalName()),
    'file_size' => $file->getSize(),
    'note' => $note,
    'submitted_at' => $submittedAt,
    'is_late' => $submittedAt->greaterThan($assignment->due_at),
]);
```

`$submittedAt` = `now()` (jam server), dibandingkan dengan `$assignment->due_at` (dibaca dari DB). Nilai ini **tidak** berasal dari request.

Pengisian tidak melalui mass-assignment dari request. Service memakai `fill([...])` dengan nilai eksplisit, dan controller hanya meneruskan `note` yang tervalidasi:

```php
// app/Http/Controllers/Mahasiswa/SubmissionController.php:14-19
[$submission, $created] = $submissionService->store(
    $assignment,
    $request->user(),
    $request->file('file'),
    $request->validated('note'),
);
```

`StoreSubmissionRequest` hanya mengizinkan `file` dan `note`:

```php
// app/Http/Requests/StoreSubmissionRequest.php:19-22
return [
    'file' => ['required', 'file', 'mimes:pdf,doc,docx,zip', 'extensions:pdf,doc,docx,zip', 'max:10240'],
    'note' => ['nullable', 'string', 'max:5000'],
];
```

Jadi `is_late`/`file_path`/`submitted_at` tidak pernah dibaca dari request. Walaupun `Submission` punya `is_late` di `$fillable` ([app/Models/Submission.php](app/Models/Submission.php:10)), tidak ada controller yang melakukan `fill($request->all())`, sehingga tidak dapat dieksploitasi dari luar.

Selain penandaan, validasi waktu juga diperiksa kembali saat submit:

```php
// app/Http/Requests/StoreSubmissionRequest.php:31
if (! $assignment->allow_late && now()->greaterThan($assignment->due_at)) {
    $validator->errors()->add('file', 'Batas waktu pengumpulan telah lewat.');
}
```

**Konsistensi timezone:** aplikasi memakai `'timezone' => 'UTC'` di [config/app.php](config/app.php) (tidak ada `APP_TIMEZONE` di `.env`), sedangkan `due_at` disimpan sebagai `dateTime` dan di-cast `datetime` di [Assignment.php](app/Models/Assignment.php:38). Karena `now()` dan perbandingan memakai acuan server yang sama (`assignment->due_at` juga di-parse Laravel di timezone app), perbandingan konsisten secara internal. Namun perlu diperhatikan: form input `due_at` ([StoreAssignmentRequest.php](app/Http/Requests/StoreAssignmentRequest.php:21) — `['required', 'date']`) di-parse pada timezone aplikasi, dan tampilan ke pengguna memakai `->format('d M Y, H:i')` tanpa konversi ke waktu lokal ([resources/views/courses/show.blade.php:80](resources/views/courses/show.blade.php:80)). Bila dosen mengira memasukkan waktu WIB sementara app menganggapnya UTC, penilaian "terlambat" bisa bergeser.

---

## Temuan & Perbaikan

1. **Ketidaksesuaian batas unggah (`max:10240` vs PHP lokal)** — `app/Http/Requests/StoreSubmissionRequest.php:20` mengizinkan 10 MB, tetapi `upload_max_filesize = 2M` dan `post_max_size = 8M` di php.ini membuat unggahan > 2 MB gagal di PHP sebelum validasi. **Saran:** samakan kebijakan, mis. set `upload_max_filesize = 12M` dan `post_max_size = 16M`, atau turunkan aturan ke `max:5120` agar selaras dengan dokumen checkpoint. Terapkan juga nilai konsisten di produksi.
2. **Timezone tidak eksplisit** — `is_late` bergantung pada kesepakatan timezone. `config/app.php` memakai `UTC`, tetapi input `due_at` dari dosen tidak dikonversi. **Saran:** set `APP_TIMEZONE=Asia/Jakarta` (atau konversi input) dan tampilkan `due_at` dalam waktu lokal agar penilaian "terlambat" intuitif. Hal ini tidak terverifikasi sebagai bug karena perbandingan internal tetap konsisten.
3. **`is_late` ada di `$fillable`** — [app/Models/Submission.php:10](app/Models/Submission.php:10). Saat ini aman karena tidak ada `fill($request->all())`. **Saran (pengerasan):** keluarkan `is_late`, `file_path`, `submitted_at` dari `$fillable` atau gunakan assignment properti eksplisit agar tidak dapat di-mass-assign jika kode berubah.
4. **Verifikasi konfigurasi server produksi** — Tidak dapat diverifikasi dari repositori. Diperiksa hanya konfigurasi lokal (Herd). **Saran:** dokumentasikan/audit `upload_max_filesize`, `post_max_size`, `client_max_body_size` (Nginx) / `LimitRequestBody` (Apache) di server produksi.

Tidak ada temuan untuk poin 1 (disk sudah privat), 2 (nama acak), 3 (memakai `mimes` + `extensions`), dan 5 (otorisasi unduhan memadai + teruji).

---

## Ringkasan

| # | Pertanyaan | Status Kode | File Terkait |
|---|---|---|---|
| 1 | Penyimpanan submission (disk privat vs `public`) | Sudah aman | `config/filesystems.php:33,41,76`; `app/Services/SubmissionService.php:20` |
| 2 | Nama berkas fisik acak | Sudah aman | `app/Services/SubmissionService.php:20,25`; `app/Http/Controllers/SubmissionDownloadController.php:16` |
| 3 | Validasi `mimes` vs `extensions` | Sudah aman | `app/Http/Requests/StoreSubmissionRequest.php:20`; `app/Http/Requests/StoreMaterialRequest.php:26-28` |
| 4 | Batas `max` & setting server | Perlu perbaikan | `app/Http/Requests/StoreSubmissionRequest.php:20`; php.ini lokal (tidak ada di repo) |
| 5 | Pencegahan unduh submission mahasiswa lain | Sudah aman | `routes/web.php:28,33`; `routes/api.php:18,34`; `app/Http/Controllers/SubmissionDownloadController.php:11-17`; `app/Policies/SubmissionPolicy.php:17-29` |
| 6 | `is_late` dihitung di server | Sudah aman (timezone perlu diperjelas) | `app/Services/SubmissionService.php:21-30`; `app/Http/Requests/StoreSubmissionRequest.php:19-31`; `config/app.php` (timezone) |

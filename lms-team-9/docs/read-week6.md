# READ Week 6

## 1. Instalasi Sanctum
Perintah `php artisan install:api` dijalankan. Mengubah `bootstrap/app.php` dengan menambahkan `api: __DIR__.'/../routes/api.php'` pada `withRouting()`. Ini membuat Laravel mendaftarkan middleware dan file routing untuk API. `config/sanctum.php` dipublikasikan dan file migrasi tabel `personal_access_tokens` dibuat.

## 2. Perbandingan CourseController Web vs API

| Fitur | Web (`CourseController`) | API (`Api\V1\CourseController`) |
| --- | --- | --- |
| File Route | `routes/web.php` | `routes/api.php` |
| Prefix & Name | Tidak ada (atau custom per web group) | `api/v1/` dan `api.v1.` |
| Middleware | `web` (session, csrf, dll) | `api` (stateless), `auth:sanctum` |
| Autentikasi | Session & Cookies | Token (Bearer Token Sanctum) |
| CSRF Protection | Ya (wajib via meta tag/input) | Tidak (stateless API token tidak butuh CSRF) |
| Output | View (Blade) | JSON (API Resource) |
| Error Handling | Redirect dengan pesan error (Flash Session) | Respon JSON dengan status code HTTP (422, 401, 403, 404, dll) |
| Model & Query | Sama (Eager loading, paginate) | Sama (dengan API Resource wrapper) |
| Route Model Binding | Sama | Sama (`->shallow()->scopeBindings()`) |
| Otorisasi (Kepemilikan) | Menggunakan `abort_unless()` | Menggunakan `abort_unless()` |

## 3. Uji Header `Accept: application/json`
- **Tanpa Header**:
  Jika memanggil endpoint terproteksi tanpa auth, Laravel mencoba melakukan redirect ke route `login` (mengembalikan 302 atau error 500 Route [login] not defined).
- **Dengan Header `Accept: application/json`**:
  Laravel langsung mengembalikan JSON dengan format:
  ```json
  {
      "message": "Unauthenticated."
  }
  ```
  Status code: 401 Unauthorized.
  Jika gagal validasi (misal POST tanpa isi), mengembalikan status 422 dengan pesan "Validation failed." beserta properti `errors`.

## 4. `route:list` (Rencana Endpoint)
Berikut adalah daftar endpoint API yang diimplementasikan sesuai kontrak Bagian 5 (atau asumsi fallback karena Bagian 5 tidak ditemukan).

| Endpoint | Method | Path |
| --- | --- | --- |
| `auth.login` | POST | `/api/v1/auth/login` |
| `auth.logout` | POST | `/api/v1/auth/logout` |
| `auth.me` | GET | `/api/v1/auth/me` |
| `courses.index` | GET | `/api/v1/courses` |
| `courses.store` | POST | `/api/v1/courses` |
| `courses.show` | GET | `/api/v1/courses/{course}` |
| `courses.update` | PUT/PATCH | `/api/v1/courses/{course}` |
| `courses.destroy` | DELETE | `/api/v1/courses/{course}` |
| `courses.materials.index` | GET | `/api/v1/courses/{course}/materials` |
| `courses.materials.store` | POST | `/api/v1/courses/{course}/materials` |
| `materials.show` | GET | `/api/v1/materials/{material}` |
| `materials.update` | PUT/PATCH | `/api/v1/materials/{material}` |
| `materials.destroy` | DELETE | `/api/v1/materials/{material}` |
| `courses.assignments.index` | GET | `/api/v1/courses/{course}/assignments` |
| `courses.assignments.store` | POST | `/api/v1/courses/{course}/assignments` |
| `assignments.show` | GET | `/api/v1/assignments/{assignment}` |
| `assignments.update` | PUT/PATCH | `/api/v1/assignments/{assignment}` |
| `assignments.destroy` | DELETE | `/api/v1/assignments/{assignment}` |
| `assignments.submissions.index` | GET | `/api/v1/assignments/{assignment}/submissions` |
| `assignments.submissions.store` | POST | `/api/v1/assignments/{assignment}/submissions` |
| `submissions.show` | GET | `/api/v1/submissions/{submission}` |
| `submissions.grade.store` | POST | `/api/v1/submissions/{submission}/grade` |
| `submissions.grade.update` | PUT | `/api/v1/submissions/{submission}/grade` |
| `submissions.grade.show` | GET | `/api/v1/submissions/{submission}/grade` |

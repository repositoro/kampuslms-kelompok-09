# READ — Minggu 6: API KampusLMS (Sanctum, Resource, Rate Limit)

**Dikerjakan:** Minggu 6  
**Cabang:** feat/toro-week-5 (diperbarui ke cakupan Week 6)

---

## 1. Instalasi Sanctum (`php artisan install:api`)

Perintah `php artisan install:api` dijalankan dan berhasil:
- Paket `laravel/sanctum` v4.3.3 dipasang via Composer.
- File `routes/api.php` diterbitkan secara otomatis.
- Migration `create_personal_access_tokens_table` ditambahkan.
- Gagal `php artisan migrate` karena MySQL tidak aktif saat audit (service Laragon perlu dinyalakan terlebih dahulu sebelum `migrate` dijalankan). Ini bukan kegagalan Sanctum, melainkan kondisi lingkungan.

**Perubahan di `bootstrap/app.php`:**
- Ditambahkan baris `api: __DIR__.'/../routes/api.php'` di blok `withRouting`.
- Ditambahkan `withExceptions` → `shouldRenderJsonWhen` agar semua response di `/api/*` selalu JSON.

**Trait `HasApiTokens`:**
- Ditambahkan ke `App\Models\User` agar Sanctum dapat membuat dan memvalidasi token.

---

## 2. Perbandingan: Web vs API

| Aspek | Web (web.php) | API (api.php) |
|---|---|---|
| **File route** | `routes/web.php` | `routes/api.php` |
| **Prefix** | `/admin`, `/dosen`, `/mahasiswa` | `/api/v1/...` |
| **Middleware group** | `web` (session, cookie) | `api` (stateless) |
| **Autentikasi** | Session + cookie Sanctum SPA | Bearer Token (`auth:sanctum`) |
| **CSRF** | Ya (form Blade) | Tidak (token di header) |
| **Output** | View Blade | JSON via API Resource |
| **Error handling** | Redirect + flash message | Status code JSON |
| **Validasi gagal** | Redirect 302 ke form | 422 JSON |
| **Kesamaan** | Model, query, route model binding, aturan kepemilikan |

---

## 3. Pengujian Header `Accept: application/json`

### Tanpa header (endpoint auth tanpa token)
```
HTTP/1.1 401 Unauthorized
```
Karena `shouldRenderJsonWhen` dikonfigurasi, response sudah JSON walau tanpa header Accept.

### Dengan header (validasi gagal)
```json
{ "message": "The email field is required.", "errors": { "email": ["..."] } }
HTTP 422
```

### Resource tidak ditemukan
```json
{ "message": "No query results for model [App\\Models\\Course] 999" }
HTTP 404
```

---

## 4. Route List `--path=api` (setelah implementasi)

Semua endpoint diverifikasi via `php artisan route:list --path=api`. Hasil mencakup:
- `POST /api/v1/auth/login` (throttle:login)
- `POST /api/v1/auth/logout`, `GET /api/v1/auth/me` (auth:sanctum)
- `GET|POST /api/v1/admin/courses`, `GET|PUT|DELETE /api/v1/admin/courses/{course}` (role:admin)
- `GET /api/v1/courses`, `GET /api/v1/courses/{course}` (role:dosen)
- `GET|POST /api/v1/courses/{course}/materials` + shallow routes (role:dosen, scopeBindings)
- `GET|POST /api/v1/courses/{course}/assignments` + shallow routes (role:dosen, scopeBindings)
- `GET|POST /api/v1/assignments/{assignment}/submissions` + shallow routes (role:mahasiswa, scopeBindings)
- `POST|PUT|GET /api/v1/submissions/{submission}/grade`

---

## 5. Temuan Utama

1. **Model sudah lengkap** — User, Course, Material, Assignment, Submission, Grade beserta seluruh relasi Eloquent sudah tersedia dari sprint sebelumnya.
2. **Sanctum tanpa tabel `personal_access_tokens`** — Migration perlu dijalankan saat DB aktif (`php artisan migrate`).
3. **IDOR ditemukan di 4 titik** — Material, Assignment (oleh Dosen lain), Submission (oleh Mahasiswa lain), dan Grade (oleh Mahasiswa yang bukan pemilik).
4. **N+1 Query** — `Model::preventLazyLoading` diaktifkan di `AppServiceProvider` untuk deteksi otomatis saat lokal.
5. **Rate limit** — 60/menit untuk API terautentikasi, 5/menit untuk login (per email+IP).

# Dokumentasi API KampusLMS

**Versi:** v1  
**Base URL:** `http://localhost:8000/api/v1`  
**Autentikasi:** Bearer Token (Sanctum)  
**Header wajib:** `Accept: application/json`

---

## Format Response

### Sukses (satu resource)
```json
{ "data": { ... } }
```

### Sukses (koleksi + paginasi)
```json
{
  "data": [ ... ],
  "links": { "first": "...", "last": "...", "prev": null, "next": "..." },
  "meta": { "current_page": 1, "last_page": 3, "per_page": 15, "total": 42 }
}
```

### Error validasi (422)
```json
{ "message": "...", "errors": { "field": ["pesan"] } }
```

### Error umum (401/403/404/429)
```json
{ "message": "..." }
```

---

## Rate Limit

| Endpoint | Batas |
|---|---|
| `POST /auth/login` | 5 request/menit per email+IP |
| Semua endpoint terautentikasi | 60 request/menit per user/IP |

Response 429 menyertakan header `Retry-After`.

---

## Alur Cepat

```bash
# 1. Login → simpan token
TOKEN=$(curl -s -X POST http://localhost:8000/api/v1/auth/login \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@test.com","password":"password"}' \
  | grep -o '"token":"[^"]*"' | sed 's/"token":"//;s/"//')

# 2. Panggil endpoint
curl -s http://localhost:8000/api/v1/courses \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# 3. Logout
curl -s -X POST http://localhost:8000/api/v1/auth/logout \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

---

## Endpoint Auth

### POST /auth/login
Login dan dapatkan token.

**Body:**

| Field | Tipe | Wajib |
|---|---|---|
| email | string | ✓ |
| password | string | ✓ |

**Contoh:**
```bash
curl -s -X POST http://localhost:8000/api/v1/auth/login \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@test.com","password":"password"}'
```

**Response 200:**
```json
{
  "data": {
    "user": { "id": 1, "name": "Admin Test", "email": "admin@test.com", "role": "admin" },
    "token": "1|plainTextToken..."
  }
}
```

**Error:** 422 (validasi), 429 (rate limit)

---

### POST /auth/logout *(Bearer token)*
Hapus token yang sedang dipakai.

```bash
curl -s -X POST http://localhost:8000/api/v1/auth/logout \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

**Response 200:** `{ "message": "Berhasil logout." }`

---

### GET /auth/me *(Bearer token)*
Informasi user yang sedang login.

```bash
curl -s http://localhost:8000/api/v1/auth/me \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

**Response 200:** `{ "data": { "id": 1, "name": "...", "email": "...", "role": "..." } }`

---

## Endpoint Courses

### GET /admin/courses *(admin)*
Daftar semua mata kuliah (paginasi).

```bash
curl -s http://localhost:8000/api/v1/admin/courses \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN_ADMIN"
```

### GET /admin/courses/{id} *(admin)*
Detail satu mata kuliah.

### POST /admin/courses *(admin)*

| Field | Tipe | Wajib |
|---|---|---|
| code | string (maks 20, unik) | ✓ |
| name | string | ✓ |
| description | string | – |
| sks | integer (1–6) | ✓ |
| lecturer_id | integer | ✓ |
| status | draft / active / archived | ✓ |

### PUT /admin/courses/{id} *(admin)*
Semua field opsional.

### DELETE /admin/courses/{id} *(admin)*
Response 204 (tanpa body).

---

## Endpoint Materials (Dosen)

### GET /courses/{course}/materials *(dosen, pemilik course)*
### POST /courses/{course}/materials *(dosen, pemilik course)*

| Field | Tipe | Wajib |
|---|---|---|
| title | string | ✓ |
| type | file / link | ✓ |
| external_url | URL | Jika type=link |
| file_path | string | Jika type=file |
| description | string | – |

### GET /materials/{id} *(dosen, pemilik)*
### PUT /materials/{id} *(dosen, pemilik)*
### DELETE /materials/{id} → 204 *(dosen, pemilik)*

---

## Endpoint Assignments (Dosen)

### GET /courses/{course}/assignments *(dosen, pemilik course)*
### POST /courses/{course}/assignments *(dosen, pemilik course)*

| Field | Tipe | Wajib |
|---|---|---|
| title | string | ✓ |
| instructions | string | ✓ |
| due_at | datetime | ✓ |
| status | draft / published | ✓ |
| max_score | integer (0–100) | – |
| allow_late | boolean | – |

### GET /assignments/{id} *(dosen, pemilik)*
### PUT /assignments/{id} *(dosen, pemilik)*
### DELETE /assignments/{id} → 204 *(dosen, pemilik)*

---

## Endpoint Submissions

### GET /assignments/{assignment}/submissions
- **Dosen (pemilik):** semua submission di assignment ini.
- **Mahasiswa:** hanya submission miliknya.

### POST /assignments/{assignment}/submissions *(mahasiswa, harus terdaftar di course)*

| Field | Tipe | Wajib |
|---|---|---|
| file_path | string | ✓ |
| original_name | string | ✓ |
| file_size | integer | ✓ |
| submitted_at | datetime | ✓ |
| note | string | – |

### GET /submissions/{id} *(mahasiswa pemilik atau dosen pemilik assignment)*
### PUT /submissions/{id} *(mahasiswa pemilik)*
### DELETE /submissions/{id} → 204 *(mahasiswa pemilik)*

---

## Endpoint Grades (Dosen memberi nilai, Mahasiswa melihat)

### POST /submissions/{submission}/grade *(dosen, pemilik assignment)*
### PUT /submissions/{submission}/grade *(dosen, pemilik assignment)*
### GET /submissions/{submission}/grade *(dosen pemilik atau mahasiswa pemilik submission)*

| Field | Tipe | Wajib |
|---|---|---|
| score | decimal | ✓ |
| graded_at | datetime | ✓ |
| feedback | string | – |

---

## Kode Error

| Kode | Arti |
|---|---|
| 401 | Tidak terautentikasi (token tidak ada / tidak valid) |
| 403 | Tidak memiliki izin akses resource ini |
| 404 | Resource tidak ditemukan |
| 422 | Validasi gagal |
| 429 | Terlalu banyak permintaan |

> **Pesan 403 tidak menyebutkan informasi pemilik, ID internal, atau peran yang dibutuhkan.**

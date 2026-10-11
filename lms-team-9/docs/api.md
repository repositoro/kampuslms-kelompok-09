# Dokumentasi API KampusLMS (v1)

## Ringkasan
- **Base URL**: `/api/v1`
- **Versi**: 1
- **Autentikasi**: Bearer Token (Sanctum)
- **Header Wajib**: `Accept: application/json`
- **Rate Limit**:
  - Global API: 60 request / menit per user/IP.
  - Login: 5 request / menit per email/IP.
- **Format Response**:
  - Sukses tunggal: `{ "data": {...} }`.
  - Sukses koleksi: `{ "data": [...], "meta": {"current_page": 1, "last_page": 5, "total": 47} }`.
  - Validasi (422): `{ "message": "Data yang diberikan tidak valid.", "errors": {...} }`.
  - Tidak berhak (403): `{ "message": "Anda tidak memiliki akses ke sumber daya ini." }`.

## Alur Cepat
1. Lakukan request `POST /api/v1/auth/login` dengan `email` dan `password`.
2. Simpan token yang didapat dari `data.token`.
3. Panggil endpoint lain dengan menyertakan header `Authorization: Bearer <token>` dan `Accept: application/json`.
4. Lakukan request `POST /api/v1/auth/logout` untuk menghapus token saat ini (logout).

## Endpoint

### 1. Auth

**POST `/api/v1/auth/login`**
- **Akses**: Publik
- **Body**: `email` (string), `password` (string).
- **Contoh Request**:
  ```bash
  curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com", "password":"password"}'
  ```

**POST `/api/v1/auth/logout`**
- **Akses**: Semua (Login)
- **Contoh Request**:
  ```bash
  curl -X POST http://localhost:8000/api/v1/auth/logout \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <token>"
  ```

**GET `/api/v1/me`**
- **Akses**: Semua (Login)
- **Contoh Request**:
  ```bash
  curl -X GET http://localhost:8000/api/v1/me \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <token>"
  ```

### 2. Courses

**GET `/api/v1/courses`**
- **Akses**: Semua (Login)
- **Contoh Request**:
  ```bash
  curl -X GET http://localhost:8000/api/v1/courses \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <token>"
  ```

**POST `/api/v1/courses`**
- **Akses**: Admin
- **Body**: `code`, `name`, `description`, `sks`, `lecturer_id`, `status`
- **Contoh Request**:
  ```bash
  curl -X POST http://localhost:8000/api/v1/courses \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{"code":"SI2514024", "name":"Pemrograman Web", "sks":3, "lecturer_id":1, "status":"active"}'
  ```

**GET `/api/v1/courses/{course}`**
- **Akses**: Semua (Login)
- **Contoh Request**:
  ```bash
  curl -X GET http://localhost:8000/api/v1/courses/1 \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <token>"
  ```

**PUT `/api/v1/courses/{course}`**
- **Akses**: Admin
- **Body**: field mata kuliah yang akan diperbarui (`code`, `name`, `description`, `sks`, `lecturer_id`, `status`)
- **Contoh Request**:
  ```bash
  curl -X PUT http://localhost:8000/api/v1/courses/1 \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{"name":"Pemrograman Web Update", "description":"Update ProWeb"}'
  ```

**DELETE `/api/v1/courses/{course}`**
- **Akses**: Admin
- **Contoh Request**:
  ```bash
  curl -X DELETE http://localhost:8000/api/v1/courses/1 \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <token>"
  ```

### 3. Materials & Assignments
- `GET /api/v1/courses/{course}/materials` (Admin, dosen pengampu, mahasiswa terdaftar)
- `POST /api/v1/courses/{course}/materials` (Admin, dosen pengampu)
- `GET /api/v1/materials/{material}` (Admin, dosen pengampu, mahasiswa terdaftar)
- `PUT /api/v1/materials/{material}` dan `DELETE /api/v1/materials/{material}` (Admin, dosen pengampu)

- `GET /api/v1/courses/{course}/assignments` (Admin, dosen pengampu; mahasiswa terdaftar hanya melihat tugas published)
- Parameter opsional: `?status=draft|published&page=1`
- `POST /api/v1/courses/{course}/assignments` (Admin, dosen pengampu)
- `GET /api/v1/assignments/{assignment}` (Admin, dosen pengampu; mahasiswa terdaftar hanya melihat tugas published)
- `PUT /api/v1/assignments/{assignment}` (Admin, dosen pengampu)
- `DELETE /api/v1/assignments/{assignment}` (Admin, dosen pengampu)

### 4. Submissions & Grades
- `GET /api/v1/assignments/{assignment}/submissions` (Admin, dosen pengampu)
- `POST /api/v1/assignments/{assignment}/submissions` (Mahasiswa)
- `GET /api/v1/submissions/{submission}` (Dosen pemilik, Mahasiswa pemilik)
- `POST /api/v1/submissions/{submission}/grade` (Dosen pemilik)
- `PUT /api/v1/submissions/{submission}/grade` (Dosen pemilik)
- `GET /api/v1/submissions/{submission}/grade` (Dosen pemilik, Mahasiswa pemilik)

## Contoh Error Response
**422 Unprocessable Entity (Validasi Gagal)**
```json
{
    "message": "Data yang diberikan tidak valid.",
    "errors": {
        "email": ["The email field is required."]
    }
}
```

**403 Forbidden**
```json
{
    "message": "Anda tidak memiliki akses ke sumber daya ini."
}
```

**401 Unauthorized**
```json
{
    "message": "Unauthenticated."
}
```

**429 Too Many Requests**
```json
{
    "message": "Terlalu banyak percobaan."
}
```

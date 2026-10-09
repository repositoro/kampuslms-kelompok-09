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
  - Sukses: `{ "data": ..., "meta": {...}, "links": {...} }` (Untuk resource tunggal kadang tanpa meta/links).
  - Error: `{ "message": "Pesan error...", "errors": { "field": ["Detail error"] } }` (HTTP 4xx/5xx).

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

**GET `/api/v1/auth/me`**
- **Akses**: Semua (Login)
- **Contoh Request**:
  ```bash
  curl -X GET http://localhost:8000/api/v1/auth/me \
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
- **Akses**: Admin, Dosen
- **Body**: `title`, `description`
- **Contoh Request**:
  ```bash
  curl -X POST http://localhost:8000/api/v1/courses \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{"title":"Pemrograman Web", "description":"Mata kuliah ProWeb"}'
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
- **Akses**: Admin, Dosen (pemilik)
- **Body**: `title`, `description`
- **Contoh Request**:
  ```bash
  curl -X PUT http://localhost:8000/api/v1/courses/1 \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{"title":"Pemrograman Web Update", "description":"Update ProWeb"}'
  ```

**DELETE `/api/v1/courses/{course}`**
- **Akses**: Admin, Dosen (pemilik)
- **Contoh Request**:
  ```bash
  curl -X DELETE http://localhost:8000/api/v1/courses/1 \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <token>"
  ```

### 3. Materials & Assignments
Sama seperti `courses` namun dengan prefix nested:
- `GET /api/v1/courses/{course}/materials` (Semua yang login)
- `POST /api/v1/courses/{course}/materials` (Dosen pemilik)
- `GET /api/v1/materials/{material}` (Semua yang login)
- `PUT /api/v1/materials/{material}` (Dosen pemilik)
- `DELETE /api/v1/materials/{material}` (Dosen pemilik)

- `GET /api/v1/courses/{course}/assignments` (Semua yang login)
- `POST /api/v1/courses/{course}/assignments` (Dosen pemilik)
- `GET /api/v1/assignments/{assignment}` (Semua yang login)
- `PUT /api/v1/assignments/{assignment}` (Dosen pemilik)
- `DELETE /api/v1/assignments/{assignment}` (Dosen pemilik)

### 4. Submissions & Grades
- `GET /api/v1/assignments/{assignment}/submissions` (Dosen pemilik, Mahasiswa)
- `POST /api/v1/assignments/{assignment}/submissions` (Mahasiswa)
- `GET /api/v1/submissions/{submission}` (Dosen pemilik, Mahasiswa pemilik)
- `POST /api/v1/submissions/{submission}/grade` (Dosen pemilik)
- `PUT /api/v1/submissions/{submission}/grade` (Dosen pemilik)
- `GET /api/v1/submissions/{submission}/grade` (Dosen pemilik, Mahasiswa pemilik)

## Contoh Error Response
**422 Unprocessable Entity (Validasi Gagal)**
```json
{
    "message": "Validation failed.",
    "errors": {
        "email": ["The email field is required."]
    }
}
```

**403 Forbidden**
```json
{
    "message": "This action is unauthorized."
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
    "message": "Too Many Attempts."
}
```

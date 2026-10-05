# Dokumentasi API KampusLMS

Semua endpoint memiliki prefix `/api/v1`.

## Otentikasi

### 1. Login
- **Method:** POST
- **URL:** `/auth/login`
- **Akses:** Publik
- **Rate Limit:** 5 requests/minute
- **Request Body:**
```json
{
  "email": "user@test.com",
  "password": "password"
}
```
- **Response Sukses (200 OK):**
```json
{
  "data": {
    "token": "1|abc...",
    "user": {
      "id": 1,
      "name": "User Name",
      "email": "user@test.com",
      "role": "mahasiswa"
    }
  }
}
```
- **Error Response (401 Unauthorized):**
```json
{
  "message": "Email atau password salah."
}
```

### 2. Logout
- **Method:** POST
- **URL:** `/auth/logout`
- **Akses:** Auth
- **Response Sukses (200 OK):**
```json
{
  "message": "Berhasil logout"
}
```

## User & Profil

### 3. Profil Saya
- **Method:** GET
- **URL:** `/me`
- **Akses:** Auth
- **Response Sukses (200 OK):**
```json
{
  "data": {
    "id": 1,
    "name": "User Name",
    "role": "mahasiswa"
  }
}
```

## Mata Kuliah (Courses)

### 4. Daftar Mata Kuliah
- **Method:** GET
- **URL:** `/courses`
- **Akses:** Auth (Mahasiswa melihat kelas yang diikuti, Dosen melihat kelas yang diajar)
- **Response Sukses (200 OK):**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Pemrograman Web",
      "lecturer": { "id": 2, "name": "Dosen A" }
    }
  ]
}
```

### 5. Detail Mata Kuliah
- **Method:** GET
- **URL:** `/courses/{id}`
- **Akses:** Auth + Scope (Harus terdaftar atau pemilik)
- **Error Response (403 Forbidden):**
```json
{
  "message": "Anda tidak memiliki akses ke sumber daya ini."
}
```

### 6. Materi Mata Kuliah
- **Method:** GET
- **URL:** `/courses/{id}/materials`
- **Akses:** Auth + Scope
- **Response Sukses:**
```json
{
  "data": [
    {
      "id": 1,
      "title": "Materi 1",
      "file_url": "http://..."
    }
  ]
}
```

### 7. Tugas Mata Kuliah
- **Method:** GET
- **URL:** `/courses/{id}/assignments`
- **Akses:** Auth + Scope
- **Parameter:** `?status=published` & `?page=1`
- **Response Sukses:**
```json
{
  "data": [ ... ],
  "meta": { "current_page": 1, "total": 10 }
}
```

## Tugas (Assignments)

### 8. Buat Tugas
- **Method:** POST
- **URL:** `/assignments`
- **Akses:** Dosen
- **Request Body:**
```json
{
  "course_id": 1,
  "title": "Tugas 1",
  "due_at": "2026-10-05 23:59:00",
  "max_score": 100,
  "status": "published"
}
```

### 9. Update Tugas
- **Method:** PUT/PATCH
- **URL:** `/assignments/{id}`
- **Akses:** Dosen (Pemilik)

### 10. Hapus Tugas
- **Method:** DELETE
- **URL:** `/assignments/{id}`
- **Akses:** Dosen (Pemilik)

## Submissions & Grading

### 11. Kumpulkan Tugas
- **Method:** POST
- **URL:** `/assignments/{id}/submissions`
- **Akses:** Mahasiswa (Terdaftar)
- **Request Body (multipart/form-data):**
  - `file`: File tugas
  - `note`: (Opsional)

### 12. Lihat Submissions Tugas
- **Method:** GET
- **URL:** `/assignments/{id}/submissions`
- **Akses:** Dosen (Pemilik)

### 13. Nilai Tugas (Grade Upsert)
- **Method:** PUT
- **URL:** `/submissions/{id}/grade`
- **Akses:** Dosen (Pemilik)
- **Request Body:**
```json
{
  "score": 95,
  "feedback": "Bagus sekali"
}
```
- **Response Sukses:**
  - `201 Created` (Jika baru dinilai pertama kali)
  - `200 OK` (Jika update nilai)

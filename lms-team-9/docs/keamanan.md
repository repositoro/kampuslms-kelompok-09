# Dokumentasi Keamanan & Titik Rawan IDOR (M2)

Dokumen ini mencatat endpoint yang perlu diuji terhadap akses lintas pengguna atau lintas mata kuliah. Route API memakai autentikasi Sanctum; route web memakai sesi login Laravel. HTTP 403 berarti pengguna terautentikasi tetapi tidak berhak. Untuk uji, isi variabel ID dari data hasil `php artisan migrate:fresh --seed`.

| Entitas | Penyerang dan target | Route aktif yang diuji | Mekanisme penahan di kode |
|---|---|---|---|
| **User** | Dosen/mahasiswa mencoba mengubah atau menghapus akun pengguna lain. | `GET /admin/users/{user}/edit`, `PUT /admin/users/{user}`, `DELETE /admin/users/{user}` (web; tidak ada API pengguna). | [routes/web.php](../routes/web.php):30-32 membatasi grup ke admin; [UserController.php](../app/Http/Controllers/UserController.php):90, 100, 133 memanggil `Gate::authorize`; [UserPolicy.php](../app/Policies/UserPolicy.php):24-31 membatasi penghapusan ke admin. |
| **Course** | Dosen mencoba mengubah atau menghapus mata kuliah, termasuk yang diampunya. Hanya admin yang memiliki CRUD mata kuliah. | `PUT /api/v1/courses/{course}`, `DELETE /api/v1/courses/{course}`. | [CourseController.php](../app/Http/Controllers/Api/V1/CourseController.php):39-56 memanggil Gate; [CoursePolicy.php](../app/Policies/CoursePolicy.php):33-35, 44-47 hanya mengizinkan admin untuk update/delete. |
| **Material** | Dosen A mencoba mengubah atau menghapus materi pada mata kuliah dosen B. | `PUT /api/v1/materials/{material}`, `DELETE /api/v1/materials/{material}`. | [UpdateMaterialRequest.php](../app/Http/Requests/UpdateMaterialRequest.php):10-14 memeriksa izin update; [MaterialController.php](../app/Http/Controllers/Api/V1/MaterialController.php):74-81 memeriksa izin delete; [MaterialPolicy.php](../app/Policies/MaterialPolicy.php):22-30 membatasi dosen ke mata kuliah sendiri. |
| **Assignment** | Dosen A mencoba mengubah atau menghapus tugas pada mata kuliah dosen B. | `PUT /api/v1/assignments/{assignment}`, `DELETE /api/v1/assignments/{assignment}`. | [UpdateAssignmentRequest.php](../app/Http/Requests/UpdateAssignmentRequest.php):10-14 memeriksa izin update; [AssignmentController.php](../app/Http/Controllers/Api/V1/AssignmentController.php):61-65 memeriksa izin delete; [AssignmentPolicy.php](../app/Policies/AssignmentPolicy.php):31-44 membatasi dosen ke mata kuliah sendiri. |
| **Submission** | Mahasiswa A mencoba melihat atau mengunduh submission milik mahasiswa B. Tidak ada route update submission; pengumpulan ulang memakai `POST` pada collection dan dibatasi pemilik/keikutsertaan. | `GET /api/v1/submissions/{submission}`, `GET /api/v1/submissions/{submission}/download`. | [SubmissionController.php](../app/Http/Controllers/Api/V1/SubmissionController.php):43-46 dan [SubmissionDownloadController.php](../app/Http/Controllers/SubmissionDownloadController.php):11-15 memanggil Gate; [SubmissionPolicy.php](../app/Policies/SubmissionPolicy.php):17-29 memeriksa pemilik serta akses tugas. |
| **Grade** | Dosen B mencoba memberi atau mengubah nilai pada submission dari mata kuliah dosen A. | `POST /api/v1/submissions/{submission}/grade`, `PUT /api/v1/submissions/{submission}/grade`. | [GradeController.php](../app/Http/Controllers/Api/V1/GradeController.php):15-29 memanggil Gate; [GradePolicy.php](../app/Policies/GradePolicy.php):17-25 memeriksa dosen pengampu saat membuat/mengubah nilai. |

## Perintah uji curl dan hasil

Gunakan `API=http://127.0.0.1:8000/api/v1` dan token hasil login masing-masing akun demo. Untuk route web User, gunakan cookie sesi web mahasiswa/dosen karena route tersebut tidak menerima token Sanctum.

### User

```sh
curl -i -b "$WEB_COOKIE" http://127.0.0.1:8000/admin/users/1/edit
curl -i -X DELETE -b "$WEB_COOKIE" -H "X-CSRF-TOKEN: $CSRF" http://127.0.0.1:8000/admin/users/1
```

Hasil yang diharapkan untuk kedua request dari sesi dosen/mahasiswa: **403**. Hasil aktual setelah seed, sesi mahasiswa demo dan CSRF valid: **403 / 403**.

### Course

```sh
curl -i -X PUT "$API/courses/$COURSE_ID" -H "Authorization: Bearer $DOSEN_TOKEN" -H "Accept: application/json" -H "Content-Type: application/json" -d '{"description":"uji IDOR"}'
curl -i -X DELETE "$API/courses/$COURSE_ID" -H "Authorization: Bearer $DOSEN_TOKEN" -H "Accept: application/json"
```

Pilih mata kuliah seed milik dosen pemegang token (ubah dan hapus tetap khusus admin). Hasil yang diharapkan: **403** untuk kedua request. Hasil aktual setelah seed, course ID `5`, token dosen demo: **403 / 403**.

### Material

```sh
curl -i -X PUT "$API/materials/$MATERIAL_ID" -H "Authorization: Bearer $DOSEN_A_TOKEN" -H "Accept: application/json" -H "Content-Type: application/json" -d '{"title":"uji IDOR"}'
curl -i -X DELETE "$API/materials/$MATERIAL_ID" -H "Authorization: Bearer $DOSEN_A_TOKEN" -H "Accept: application/json"
```

Target adalah materi sementara yang dibuat pada mata kuliah dosen B untuk uji, lalu dihapus admin. Hasil yang diharapkan: **403** untuk kedua request. Hasil aktual, material ID `1`, token dosen demo: **403 / 403**.

### Assignment

```sh
curl -i -X PUT "$API/assignments/$ASSIGNMENT_ID" -H "Authorization: Bearer $DOSEN_A_TOKEN" -H "Accept: application/json" -H "Content-Type: application/json" -d '{"title":"uji IDOR"}'
curl -i -X DELETE "$API/assignments/$ASSIGNMENT_ID" -H "Authorization: Bearer $DOSEN_A_TOKEN" -H "Accept: application/json"
```

Target adalah tugas seed pada mata kuliah dosen B. Hasil yang diharapkan: **403** untuk kedua request. Hasil aktual, assignment ID `1`, token dosen demo: **403 / 403**.

### Submission

```sh
curl -i "$API/submissions/$SUBMISSION_ID" -H "Authorization: Bearer $MAHASISWA_A_TOKEN" -H "Accept: application/json"
curl -i "$API/submissions/$SUBMISSION_ID/download" -H "Authorization: Bearer $MAHASISWA_A_TOKEN" -H "Accept: application/json"
```

Pilih submission seed milik mahasiswa B yang berbeda dari pemegang token mahasiswa A. Hasil yang diharapkan: **403** untuk kedua request. Hasil aktual, submission ID `2` milik user ID `8`, token mahasiswa demo: **403 / 403**.

### Grade

```sh
curl -i -X POST "$API/submissions/$SUBMISSION_BARU_ID/grade" -H "Authorization: Bearer $DOSEN_B_TOKEN" -H "Accept: application/json" -H "Content-Type: application/json" -d '{"score":80}'
curl -i -X PUT "$API/submissions/$SUBMISSION_SUDAH_DINILAI_ID/grade" -H "Authorization: Bearer $DOSEN_B_TOKEN" -H "Accept: application/json" -H "Content-Type: application/json" -d '{"score":80}'
```

Kedua submission berasal dari mata kuliah dosen A; satu belum dinilai dan satu sudah dinilai. Hasil yang diharapkan: **403** untuk kedua request. Hasil aktual, submission ID `7` dan `1`, token dosen demo yang bukan pengampu: **403 / 403**.

## Riwayat temuan

Seluruh uji di atas tertahan sesuai hasil yang diharapkan. Request hapus user dan course menggunakan data/session/token uji; data seed utama tetap berjumlah 34 user, 5 course, 15 assignment, dan 150 submission setelah pengujian. Material sementara dihapus admin dengan respons 204.

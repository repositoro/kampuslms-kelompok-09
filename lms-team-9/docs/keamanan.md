# Dokumentasi Keamanan & Matriks Mitigasi IDOR (M2)

Dokumen ini mencatat titik rawan *Insecure Direct Object Reference* (IDOR) pada aplikasi KampusLMS beserta mekanisme penutupannya menggunakan Laravel Policy dan Query Level Scoping.

| Entitas | Endpoint / Aksi Rawan | Potensi IDOR | Mekanisme Penutupan (Policy & Query Scope) |
|---|---|---|---|
| **User** | `GET /admin/users/{user}/edit`<br>`DELETE /admin/users/{user}` | Pengguna biasa mencoba mengubah atau menghapus akun pengguna lain via manipulasi ID pada URL. | `UserPolicy@update`, `UserPolicy@delete`<br>Diproteksi `Gate::authorize()` & Middleware `role:admin`. |
| **Course** | `GET /courses/{course}/edit`<br>`PUT /courses/{course}` | Dosen A mencoba mengubah data mata kuliah milik Dosen B dengan mengganti ID course di URL. | `CoursePolicy@update`<br>Memeriksa `$course->lecturer_id === $user->id` atau `$user->role === 'admin'`. |
| **Material** | `GET /dosen/materials/{material}/edit`<br>`DELETE /dosen/materials/{material}` | Dosen A mencoba mengubah atau menghapus materi milik Dosen B. | `MaterialPolicy@update`, `MaterialPolicy@delete`<br>Memeriksa `$material->course->lecturer_id === $user->id` atau `$user->role === 'admin'`. |
| **Assignment** | `GET /dosen/assignments/{assignment}/edit`<br>`DELETE /dosen/assignments/{assignment}` | Dosen A mencoba mengubah atau menghapus tugas perkuliahan milik Dosen B. | `AssignmentPolicy@update`, `AssignmentPolicy@delete`<br>Memeriksa `$assignment->course->lecturer_id === $user->id` atau `$user->role === 'admin'`. |
| **Submission** | `GET /mahasiswa/submissions/{submission}`<br>`PUT /mahasiswa/submissions/{submission}` | Mahasiswa A melihat/mengubah berkas *submission* milik Mahasiswa B dengan mengganti ID submission di URL. | `SubmissionPolicy@view`, `SubmissionPolicy@update`<br>Query scoped: `$submission->user_id === $user->id`. |
| **Grade** | `POST /dosen/submissions/{submission}/grade` | Dosen B memberi/mengubah nilai pada pengumpulan tugas dari kelas Dosen A. | `GradePolicy@create`, `GradePolicy@update`<br>Memeriksa apakah Dosen yang login adalah pengampu dari mata kuliah terkait. |
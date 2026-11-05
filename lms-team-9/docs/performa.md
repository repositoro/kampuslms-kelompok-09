# Performa KampusLMS

## Metode pengukuran

Pengukuran menggunakan jumlah statement database per request yang ditangkap melalui `DB::listen` pada Feature Test Laravel, dengan satu set fixture kecil yang sama sebelum dan sesudah optimasi. Tes berjalan lokal memakai SQLite in-memory dan cache `array`; angka ini membandingkan query aplikasi secara konsisten, bukan mengukur latency jaringan atau mengklaim hasil production. Nilai dapat berbeda pada MySQL/PHP-FPM dan saat cache production memakai backend database.

Halaman yang memiliki data daftar diuji kembali dengan lebih dari satu halaman data untuk memastikan link pagination muncul dan relasi tidak memicu lazy loading. Tabel query dasar memakai fixture kecil yang sama sebelum/sesudah. Tes khusus rekap nilai membuat 20 nilai dan menghitung query hanya selama request halaman tersebut.

## Tabel pengukuran sebelum dan sesudah

Jumlah query SQL per request; fixture dasar berisi satu course, satu material, satu assignment, satu submission, dan satu grade.

| Halaman utama | Query sebelum | Query sesudah | Perubahan |
|---|---:|---:|---:|
| Dashboard admin | 16 | 5 | -68,8% |
| Daftar pengguna admin | 21 | 3 | -85,7% |
| Daftar mata kuliah admin | 13 | 4 | -69,2% |
| Detail mata kuliah dosen | 26 | 10 | -61,5% |
| Dashboard dosen | 12 | 4 | -66,7% |
| Daftar materi dosen | 17 | 5 | -70,6% |
| Daftar tugas dosen | 17 | 5 | -70,6% |
| Detail tugas/dosen | 26 | 9 | -65,4% |
| Monitoring submission dosen | 17 | 8 | -52,9% |
| Rekap nilai per mata kuliah | 17 | 7 | -58,8% |
| Dashboard mahasiswa | 16 | 8 | -50,0% |
| Daftar mata kuliah mahasiswa | 13 | 4 | -69,2% |
| Riwayat nilai mahasiswa | 15 | 6 | -60,0% |
| Daftar notifikasi | 11 | 2 | -81,8% |
| **Total dari 14 request terukur** | **237** | **80** | **-66,2%** |

Angka sesudah diukur ulang oleh `tests/Feature/PerformanceBudgetTest.php`. Pengukuran 20 record pada halaman rekap nilai menghasilkan **7 query**, di bawah target 15. Tes multi-record di `tests/Feature/PaginationAndLazyLoadingTest.php` memeriksa link pagination untuk daftar pengguna, course, materi, tugas, peserta/opsi enrollment, submission, nilai, notifikasi, dan dashboard, sekaligus merender halaman terkait dengan pencegahan lazy loading aktif.

## Lazy loading dan N+1

`AppServiceProvider` mengaktifkan `Model::preventLazyLoading(! $this->app->isProduction())`: lazy loading dilarang pada local/testing dan tetap mengikuti perilaku production Laravel. Relasi yang dibutuhkan view dimuat eksplisit melalui `with()` atau `withCount()`. Tes performa berjalan dengan pencegahan lazy loading aktif, termasuk fixture rekap nilai lebih dari satu halaman.

Perbaikan yang relevan:

- Rekap nilai memakai `with(['submission.student', 'submission.assignment'])` atau `with(['submission.assignment.course'])`, lalu `paginate(15)`.
- Daftar mata kuliah memakai eager loading `lecturer`; dashboard dosen menggunakan `withCount(['students', 'assignments'])`.
- Daftar tugas dan monitoring submission memuat relasi siswa/nilai per halaman, bukan mengakses relasi yang belum dimuat berulang kali.
- Detail mata kuliah memuat dosen dan mengambil peserta, materi, tugas, serta pilihan mahasiswa tersedia sebagai paginator terpisah.
- Composer jumlah notifikasi hanya terpasang pada `layouts.app`, tidak pada setiap component/subview. Ini menghapus query notifikasi berulang saat satu halaman merender banyak komponen.
- Penanda semua notifikasi dibaca menggunakan update query, bukan memuat seluruh notifikasi ke memory.

## Index tambahan

Migrasi `2026_11_03_000000_add_performance_indexes.php` menambahkan:

| Index | Query yang diuntungkan |
|---|---|
| `assignments(course_id, status, due_at)` | Tugas mendatang mahasiswa: filter mata kuliah/status, lalu batas waktu dan urutan tenggat. |
| `submissions(user_id, assignment_id)` | Riwayat nilai mahasiswa dan pencarian submission berdasarkan pemilik. |
| `grades(graded_at, id)` | Urutan terbaru yang konsisten pada rekap nilai dosen/mahasiswa. |

Komentar di dalam migrasi menghubungkan setiap index dengan query yang dirancang untuk dibantu. Index tidak menggantikan pemeriksaan query plan pada dataset production; ukur kembali setelah migrasi di staging.

## Pagination

Semua daftar berbasis tabel/relasi utama memakai `paginate()` (15 item untuk daftar standar; 10 untuk panel dashboard/detail) dan menampilkan link halaman. Termasuk daftar pengguna, course, materi, tugas, submission, nilai, notifikasi, peserta kelas, pilihan mahasiswa/dosen pada form, dan koleksi course di dashboard. Endpoint collection API yang sudah memakai `paginate()` tetap dipaginasi.

Dashboard mahasiswa hanya mengambil lima tugas terdekat sebagai widget ringkas; ini adalah batas eksplisit, bukan pemuatan seluruh tabel. Jumlah pengguna/course di dashboard admin menggunakan agregasi `count()`, bukan daftar seluruh baris.

## Cache dashboard dan invalidasi

Bagian yang dicache selama paling lama lima menit:

- Statistik dashboard admin.
- Daftar mata kuliah dan jumlah submission belum dinilai pada dashboard dosen.
- Daftar mata kuliah dashboard mahasiswa.

`DashboardCache` memakai versi cache global. Observer `User`, `Course`, `Assignment`, `Submission`, dan `Grade` menaikkan versi saat model disimpan/dihapus/dipulihkan; perubahan enrollment pada pivot juga menaikkan versi secara eksplisit di `EnrollmentController`. Karena key memakai versi, request berikutnya membentuk cache baru tanpa perlu menghapus semua isi cache. TTL lima menit membatasi sisa key lama.

**Bagian yang sengaja tidak dicache:**

- **Rekap nilai**: harus menampilkan perubahan nilai secepatnya dan isinya sensitif terhadap pengguna/course; pagination serta query relasi sudah mengurangi biayanya.
- **Notifikasi dan unread count**: status dibaca harus segera konsisten setelah aksi pengguna.
- **Tugas mendatang mahasiswa**: hasil bergantung pada waktu sekarang/deadline dan status submission, jadi cache dapat membuat status tenggat terlihat usang.
- **Halaman detail/list materi dan tugas**: perubahan konten dan pemeriksaan authorization perlu berlaku langsung; halaman tersebut dipaginasi dan eager-loaded sebagai gantinya.

Tes `tests/Feature/DashboardCacheTest.php` memverifikasi statistik admin berubah setelah user baru dibuat dan jumlah mahasiswa pada dashboard dosen berubah setelah enrollment.

## Penggunaan `env()`

Audit source PHP menemukan pemanggilan `env()` hanya pada `config/`. Kode aplikasi harus mengambil nilai melalui `config()`; konfigurasi environment baru dibaca oleh file konfigurasi Laravel.

## Checklist performa

- [x] `docs/performa.md` mencatat query sebelum/sesudah untuk 14 halaman utama.
- [x] Lazy loading dicegah pada non-production dan tes performa memastikan guard aktif.
- [x] Relasi halaman di-eager-load; tes multi-record memeriksa daftar utama dan halaman rekap nilai.
- [x] Migrasi index tambahan menyebut query yang dibantu.
- [x] Daftar utama dipaginasi dan menyediakan navigasi halaman.
- [x] Cache dashboard memiliki invalidasi model dan perubahan enrollment, dengan bagian yang tidak dicache dijelaskan.
- [x] Pemanggilan `env()` di luar `config/` tidak ditemukan.
- [x] Rekap nilai course: **7 query dengan 20 nilai**, target **< 15**.

Angka query di atas adalah hasil lokal otomatis. Ulangi pengukuran pada staging dengan database, volume data, PHP runtime, dan cache store yang mendekati production sebelum menjadikannya SLA.

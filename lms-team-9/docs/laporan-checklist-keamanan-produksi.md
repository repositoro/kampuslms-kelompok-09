# Laporan Checklist Keamanan Produksi

**Tanggal pemeriksaan:** 3 November 2026  
**Cakupan:** pemeriksaan source code dan konfigurasi lokal KampusLMS. Tidak ada domain, akses server, atau kredensial produksi yang diberikan; hasil lokal tidak dapat dianggap sebagai hasil produksi.

## Ringkasan

Aplikasi **belum dapat dinyatakan siap produksi**. Beberapa nilai lingkungan lokal bertentangan dengan persyaratan produksi, sementara pemeriksaan yang hanya bisa dilakukan pada server (HTTPS, pemblokiran berkas sensitif, worker Supervisor, dan pengujian otorisasi production) belum dijalankan. Tidak ada nilai rahasia dari `.env` yang dicatat di laporan ini.

Server lokal tidak dapat dijangkau selama pemeriksaan: `http://127.0.0.1:8000/` timeout dan `http://127.0.0.1:8080/` menolak koneksi. Karena itu tidak ada respons HTTP aplikasi yang dapat digunakan sebagai bukti deployment.

## Checklist

| No. | Persyaratan | Status | Bukti dan alasan |
|---:|---|---|---|
| 1 | `APP_DEBUG=false` | **Gagal di lokal** | `.env` lokal berisi `APP_DEBUG=true`. Jangan mengubah `.env` lokal secara membabi buta; tetapkan `false` pada environment produksi. |
| 2 | `APP_ENV=production` | **Gagal di lokal** | `.env` lokal memakai `APP_ENV=local`. Produksi harus memiliki nilai `production`. |
| 3 | `curl -I https://domain/.env` menghasilkan 404 atau 403 | **Belum diuji** | Tidak ada domain produksi. Probe lokal tidak terhubung, sehingga konfigurasi document root/web server belum terbukti menolak berkas ini. |
| 4 | `curl -I https://domain/storage/logs/laravel.log` menghasilkan 404 atau 403 | **Belum diuji** | Tidak ada domain produksi atau server yang dapat dijangkau. |
| 5 | HTTPS aktif dan HTTP dialihkan ke HTTPS | **Belum diuji** | Tidak ada hostname produksi untuk memeriksa sertifikat dan redirect. URL lokal menggunakan HTTP dan bukan bukti konfigurasi production. |
| 6 | `SESSION_SECURE_COOKIE=true` | **Gagal di lokal** | `SESSION_SECURE_COOKIE` tidak disetel dalam `.env` lokal. [config/session.php](../config/session.php) mengambil nilai dari environment; tetapkan `true` pada deployment HTTPS. |
| 7 | Password database bukan password lokal dan bukan `password` | **Gagal di lokal** | `.env` lokal memakai user database `root` dan password kosong. Ini hanya bukti konfigurasi lokal, bukan bukti nilai produksi. Gunakan akun/database produksi dengan kredensial kuat dan terpisah. |
| 8 | Password akun demo berbeda dari contoh dokumentasi | **Gagal di lokal; produksi belum terbukti** | Tidak ada `DEMO_ACCOUNT_PASSWORD` pada `.env` lokal. Seeder memakai fallback development yang disebut di dokumentasi; README juga menyatakan production harus memakai secret non-default. Atur secret khusus produksi; jangan menaruh nilainya di repository atau laporan. |
| 9 | Skrip otorisasi minggu 7 dijalankan terhadap server produksi dan semua tes lolos | **Belum terpenuhi** | Suite lokal lulus, tetapi memakai SQLite in-memory dari `phpunit.xml`. `scripts/test-authz.sh` menjalankan `php artisan test` lokal dan `route:list`; skrip ini tidak menerima target server production. Tes ini bukan hasil dari server produksi. Jalankan hanya dengan akun dan data uji yang aman; hindari operasi mutasi pada data nyata. |
| 10 | Submission tidak dapat diunduh tanpa login; diuji dari jendela penyamaran | **Lulus pada tes lokal; production belum diuji** | Route download berada di grup middleware `auth` pada [routes/web.php](../routes/web.php), dan [SubmissionWorkflowTest.php](../tests/Feature/SubmissionWorkflowTest.php) kini memastikan pengguna anonim diarahkan ke login. Ini setara tes aplikasi, bukan tes browser incognito terhadap production. |
| 11 | Queue worker berjalan melalui Supervisor; dibuktikan dengan `supervisorctl status` | **Belum diuji** | Tidak ada bukti akses host Linux/production atau output `supervisorctl status`. Konfigurasi aplikasi lokal memakai queue database; status worker harus dibuktikan pada host deployment. |
| 12 | `upload_max_filesize` dan `post_max_size` sesuai validasi aplikasi | **Belum sesuai di lokal; production belum diuji** | `php.ini` yang dilampirkan menetapkan `upload_max_filesize=2G` dan `post_max_size=2G`, sedangkan [StoreSubmissionRequest.php](../app/Http/Requests/StoreSubmissionRequest.php) membatasi submission sampai `20480` KB (20 MB). Aplikasi tetap memvalidasi batas 20 MB, tetapi batas PHP 2 GB terlalu longgar dan tidak memenuhi rekomendasi penyelarasan. Atur runtime PHP yang dipakai aplikasi; production belum dapat diverifikasi. |

## Yang dilakukan dan hasilnya

1. Memeriksa konfigurasi `.env` secara selektif dan menyensor password. Ini membedakan setting lokal dari persyaratan deployment tanpa menyalin secret ke laporan.
2. Memeriksa route download, disk penyimpanan, policy/controller, validasi file, konfigurasi session, seeder akun demo, dan skrip otorisasi.
3. Membaca `php.ini` yang dilampirkan dan membandingkannya dengan PHP CLI lokal; keduanya menunjukkan batas upload 2G. Nilai ini belum tentu sama dengan PHP-FPM/web server.
4. Mencoba akses HTTP hanya ke alamat loopback lokal, bukan ke domain yang tidak diberikan.
5. Menjalankan suite Laravel sebelum perubahan tes: **65 tes lulus, 434 assertion**.
6. Menambahkan assertion anonim pada tes unduhan submission; tes terkait sesudah perubahan: **6 tes lulus, 59 assertion**. Suite lengkap dijalankan kembali sesudah perubahan: **65 tes lulus, 436 assertion**.

## Perubahan file

| File | Perubahan | Alasan |
|---|---|---|
| [tests/Feature/SubmissionWorkflowTest.php](../tests/Feature/SubmissionWorkflowTest.php) | Menambahkan pemeriksaan bahwa request unduh submission tanpa login diarahkan ke halaman login. | Menguji secara eksplisit butir “tidak bisa diunduh tanpa login” pada level aplikasi, melengkapi tes kepemilikan submission yang sudah ada. |
| [docs/laporan-checklist-keamanan-produksi.md](laporan-checklist-keamanan-produksi.md) | Menambahkan checklist, status berbasis bukti, prosedur langkah demi langkah untuk 12 butir, kriteria lulus, daftar bukti akhir, serta analisis `php.ini` yang dilampirkan. | Agar pemilik server dapat menyelesaikan dan membuktikan setiap persyaratan tanpa menganggap pemeriksaan lokal sebagai verifikasi production. |

File `.env` **tidak diubah**: file itu konfigurasi lokal dan bukan tempat yang aman untuk menebak atau menaruh kredensial produksi. Tidak ada perubahan pada konfigurasi server karena host/domain production tidak tersedia.

## Panduan memenuhi setiap checklist

Langkah di bawah ditujukan untuk pemilik/deployer server. Ganti `domain-anda` dengan hostname resmi dan jalankan perintah host hanya pada mesin yang dikelola tim. Jangan tempel `.env`, password, token, cookie, atau output konfigurasi rahasia ke laporan. Checklist baru boleh ditandai **Lulus** setelah bukti production nyata disimpan.

### 1. `APP_DEBUG=false`

1. Pada pengelola secret/environment deployment, tetapkan `APP_DEBUG=false` (jangan edit `.env` lokal lalu menganggap server ikut berubah).
2. Dari direktori release Laravel di host, bangun ulang cache konfigurasi:

   ```sh
   php artisan config:clear
   php artisan config:cache
   php artisan tinker --execute="dump(config('app.debug'))"
   ```

3. Pastikan hasil terakhir `false`. Lakukan deploy/reload sesuai platform dan simpan hasil boolean itu, bukan isi `.env`.
4. Verifikasi halaman error publik tidak menampilkan stack trace. Jangan sengaja membuat error sensitif di production untuk tes.

**Mengapa:** debug dapat mengungkap stack trace, path, query, dan detail konfigurasi kepada pengunjung.

### 2. `APP_ENV=production`

1. Tetapkan `APP_ENV=production` pada environment release yang melayani trafik.
2. Jalankan `php artisan config:clear` lalu `php artisan config:cache`.
3. Verifikasi `php artisan tinker --execute="dump(config('app.env'))"` menghasilkan `production`; simpan hasilnya bersama ID release/tanggal deploy.

**Mengapa:** Laravel dan paket dapat mengubah perilaku berdasarkan environment. Konfigurasi `testing`/`local` bukan bukti server production sudah benar.

### 3. Tolak akses publik ke `/.env`

1. Pastikan web server menunjuk document root ke folder `public` Laravel, bukan root repository.
2. Pastikan aturan web server menolak dotfiles (kecuali file well-known yang memang dibutuhkan) dan `.env` tidak berada di document root.
3. Dari luar host, jalankan:

   ```sh
   curl -sS -I --max-time 10 https://domain-anda/.env
   ```

4. Terima hanya `403` atau `404`; `200` berarti gagal. Simpan status HTTP/tanggal tanpa body response.

**Mengapa:** `.env` berisi konfigurasi dan mungkin kredensial; menyembunyikan file di level aplikasi saja tidak cukup jika document root salah.

### 4. Tolak akses publik ke log Laravel

1. Pastikan `storage` berada di luar document root; jangan membuat symlink `public/storage` ke `storage` secara keseluruhan. Symlink Laravel yang diperbolehkan hanya ke `storage/app/public`.
2. Tambahkan aturan deny pada web server untuk path log jika diperlukan.
3. Jalankan dari luar host:

   ```sh
   curl -sS -I --max-time 10 https://domain-anda/storage/logs/laravel.log
   ```

4. Terima hanya `403` atau `404`, bukan `200`. Catat status saja, bukan isi log.

**Mengapa:** log dapat berisi alamat email, detail request, stack trace, dan data sensitif lain.

### 5. HTTPS aktif dan HTTP dialihkan

1. Pasang sertifikat TLS yang valid pada proxy/web server dan pastikan aplikasi menerima skema HTTPS dengan benar.
2. Arahkan semua HTTP ke HTTPS di load balancer/web server (301 atau 308; jangan mengandalkan halaman aplikasi).
3. Periksa:

   ```sh
   curl -sS -I --max-time 10 http://domain-anda/
   curl -sS -I --max-time 10 https://domain-anda/
   ```

4. Respons HTTP harus redirect ke URL `https://...`; permintaan HTTPS harus berhasil tanpa error sertifikat. Simpan status dan `Location`, jangan hanya screenshot address bar.

**Mengapa:** login dan cookie sesi tidak aman jika kredensial/sesi dapat dikirim melalui koneksi HTTP.

### 6. `SESSION_SECURE_COOKIE=true`

1. Setelah HTTPS berfungsi, tetapkan `SESSION_SECURE_COOKIE=true` pada environment production.
2. Bangun ulang cache konfigurasi (`php artisan config:clear` lalu `php artisan config:cache`) dan verifikasi `config('session.secure')` bernilai `true` melalui Tinker.
3. Login menggunakan akun uji, periksa header `Set-Cookie` di browser developer tools, dan pastikan cookie sesi memiliki flag `Secure` serta `HttpOnly`. Jangan menyalin nilai cookie ke laporan.

**Mengapa:** flag `Secure` mencegah browser mengirim cookie sesi melalui HTTP. `HttpOnly` mengurangi akses cookie oleh JavaScript.

### 7. Password database production

1. Buat akun database khusus aplikasi dengan hak minimum yang diperlukan; jangan gunakan `root`/administrator.
2. Buat password acak unik yang tidak sama dengan password lokal atau `password`; masukkan melalui secret manager deployment, bukan repository, chat, atau dokumen.
3. Pastikan koneksi aplikasi berhasil menggunakan `php artisan migrate:status` pada release production. Periksa nama user dan kebijakan secret di console database/secret manager secara privat; jangan echo atau merekam nilai password.
4. Simpan bukti berupa nama akun non-root, hak akses minimum, dan verifikasi secret manager menyimpan secret yang ditetapkan—tanpa nilainya.

**Mengapa:** password lokal/default mudah ditebak dan akun berprivilege tinggi memperbesar dampak kebocoran.

### 8. Password akun demo

1. Tentukan apakah akun demo perlu ada di production. Jika tidak diperlukan, jangan aktifkan atau publikasikan akun demo.
2. Jika tetap diperlukan, set `DEMO_ACCOUNT_PASSWORD` sebagai secret non-default melalui secret manager; nilainya harus berbeda dari contoh dokumentasi dan password lokal.
3. Konfigurasi aplikasi mengambil nilai ini melalui `config('app.demo_account_password')`. Verifikasi hanya bahwa secret ada dan bukan nilai default, tanpa menampilkan nilainya.
4. **Perhatian:** `DemoAccountSeeder` meng-hash ulang password akun demo setiap kali seeder dijalankan. Uji perubahan di staging dahulu; jangan menjalankan seeder pada production tanpa persetujuan karena dapat mengganti password akun demo yang sedang dipakai.
5. Simpan bukti pemeriksaan secret (nama secret/status rotasi) tanpa menyalin secret itu.

**Mengapa:** contoh dokumentasi bersifat publik dan bukan kredensial production.

### 9. Tes otorisasi minggu 7 terhadap deployment

1. Jalankan dahulu `php artisan test` dan review `scripts/test-authz.sh` pada staging. Suite PHPUnit memakai SQLite in-memory dari `phpunit.xml`.
2. **Jangan menjalankan `scripts/test-authz.sh` apa adanya di production.** Skrip menjalankan tes lokal (tidak menerima URL server) dan menghapus cache konfigurasi/rute di akhir; skrip itu tidak menguji server production dan dapat mengganggu deployment.
3. Minta persetujuan pemilik production dan siapkan akun/record uji khusus yang tidak berisi data nyata. Untuk mutasi, gunakan staging clone; bila dosen memang meminta tes live, pastikan ada backup, jendela tes, fixture yang dapat dibuang, serta rencana pemulihan yang disetujui.
4. Jalankan kasus akses lintas-role minggu 7 melalui endpoint deployment yang dituju: identitas tanpa login, mahasiswa lain, dosen bukan pengampu, dan role yang tidak berhak harus ditolak; pemilik/pengampu yang berhak harus tetap dapat memakai fiturnya. Catat endpoint, role uji, kode status yang diharapkan/aktual, dan hasil. Jangan mencatat token/cookie.
5. Karena skrip yang ada bukan remote runner, menandai butir ini lulus memerlukan bukti dari test harness HTTP terkontrol yang memang diarahkan ke hostname production atau bukti pelaksanaan tes manual yang disetujui. Jangan menyebut hasil lokal sebagai hasil production.

**Mengapa:** tes otorisasi yang hanya memakai database testing tidak membuktikan konfigurasi route, middleware, dan deployment yang sedang melayani pengguna.

### 10. Uji unduh submission dari jendela incognito

1. Siapkan submission milik akun uji yang memang boleh diakses, dan catat URL unduhnya tanpa membagikan berkas.
2. Buka jendela InPrivate/Incognito yang belum login ke production, tempel URL unduh tersebut.
3. Pastikan browser diarahkan ke login atau menerima penolakan; file tidak boleh terunduh/ditampilkan.
4. Login sebagai pemilik yang berwenang pada sesi normal dan pastikan unduh berhasil. Dengan akun uji berbeda, pastikan unduh ditolak juga.
5. Simpan bukti berupa hasil redirect/status dan hasil tes tanpa cookie, token, atau isi submission.

**Mengapa:** tes feature lokal membuktikan middleware/policy pada kode, sedangkan tes incognito membuktikan jalur web production tidak melewati proteksi.

### 11. Queue worker dengan Supervisor

1. Pada host Linux production, pasang Supervisor dan daftarkan program worker Laravel untuk direktori release serta koneksi queue yang dipakai aplikasi (`QUEUE_CONNECTION`).
2. Konfigurasi worker agar berjalan sebagai user aplikasi non-root, otomatis start/restart, memakai jumlah proses/timeout yang sesuai, dan mencatat stdout/stderr ke log yang tidak publik.
3. Jalankan:

   ```sh
   sudo supervisorctl reread
   sudo supervisorctl update
   sudo supervisorctl status
   ```

4. Pastikan worker KampusLMS berstatus `RUNNING`, kirim satu job uji yang tidak mengubah data penting, lalu pastikan job diproses. Simpan output status dan bukti job; redaksi path/username jika sensitif.
5. Setelah deploy, restart worker secara terkendali agar memakai kode release baru.

**Mengapa:** konfigurasi queue `database` saja tidak menjalankan worker. Tanpa process manager, job notifikasi dapat menumpuk dan tidak diproses.

### 12. Batas upload PHP dan validasi aplikasi

1. Validasi aplikasi saat ini membatasi submission hingga **20 MB** (`max:20480` KB). `php.ini` yang dilampirkan memberi batas **2 GB** untuk `upload_max_filesize` dan `post_max_size`, jauh lebih besar dari batas aplikasi.
2. Untuk menyelaraskan batas dengan validasi, usulan awalnya `upload_max_filesize=20M` dan `post_max_size=24M` (lebih besar untuk overhead multipart). Pastikan batas request web server/proxy setidaknya sebesar `post_max_size`. Tim boleh menetapkan batas yang lebih kecil sesuai kebijakan, tetapi jangan sampai upload 20 MB yang didukung aplikasi ditolak lapisan PHP/proxy.
3. Ubah file konfigurasi runtime lokal hanya jika memang ingin mengubah Laragon; untuk production ubah konfigurasi PHP-FPM/runtime web yang benar-benar dipakai. Restart/reload layanan yang sesuai. File `php.ini` yang dilampirkan tidak diedit karena merupakan snapshot lampiran dan belum membuktikan konfigurasi production.
4. Periksa nilai efektif PHP-FPM dari host/config runtime (jangan membuat halaman `phpinfo()` publik). Cocokkan seluruh lapisan: proxy/load balancer, web server, PHP-FPM, dan Laravel. Nilai PHP CLI bukan bukti PHP-FPM production.
5. Dengan akun uji, unggah file valid di bawah/sekitar 20 MB dan pastikan berhasil; unggah file di atas batas dan pastikan ditolak dengan pesan yang wajar. Pastikan request yang melewati `post_max_size` tidak dianggap sukses.
6. Simpan nilai efektif dan hasil dua tes ukuran. Jangan unggah data pribadi/berkas produksi saat validasi.

**Mengapa:** batas PHP/proxy dapat menolak request sebelum Laravel menjalankan validasi; selisih konfigurasi menghasilkan kegagalan yang membingungkan atau batas yang tidak sesuai kebijakan.

### Catatan tambahan dari `php.ini` yang dilampirkan

Selain batas upload, file tersebut memuat `display_errors=On`, `expose_php=On`, dan `max_execution_time=36000`. Ini bukan bukti nilai production, tetapi untuk deployment production pastikan error ditampilkan melalui halaman generik Laravel (gunakan `APP_DEBUG=false` dan `display_errors=Off`), pertimbangkan `expose_php=Off`, dan atur waktu eksekusi berdasarkan kebutuhan nyata aplikasi, bukan 10 jam tanpa alasan. Terapkan serta verifikasi pada PHP runtime web production; jangan mengubah konfigurasi lokal bersama tanpa persetujuan.

## Bukti akhir yang perlu dilampirkan

- Tanggal, hostname, ID release, dan pihak yang menjalankan pemeriksaan.
- Status HTTP dua URL sensitif, serta status redirect HTTP dan hasil HTTPS/TLS.
- Nilai boolean environment/session tanpa isi `.env` atau secret.
- Bukti akun database non-root dan secret demo non-default tanpa nilai kredensial.
- Hasil tes otorisasi production/staging yang diberi label jelas, termasuk role uji dan kode status.
- Hasil tes unduh incognito, `supervisorctl status`, dan tes upload pada batas ukuran.
- Catat butir gagal sebagai gagal dan blok submission sampai diperbaiki serta diuji ulang; jangan mengubah status menjadi lulus hanya berdasarkan konfigurasi yang belum diverifikasi.

## Catatan dari lampiran

Lampiran memperlihatkan halaman default Laravel di `localhost:8080` dan satu error `View [selamat tinggal] not found` pada `127.0.0.1:8000`. Kedua alamat lokal tidak dapat dijangkau saat pemeriksaan; string/view tersebut juga tidak ditemukan pada route proyek yang diperiksa. Pastikan document root web server menunjuk ke `lms-team-9/public`, proses yang berjalan memakai proyek/release yang benar, lalu reproduksi URL penyebab error dan periksa log lokal. Jangan membuat view generik `selamat tinggal` hanya untuk menyembunyikan exception; perbaiki route/controller yang meminta view tersebut setelah sumbernya teridentifikasi.

# KampusLMS

Aplikasi Learning Management System untuk proyek mata kuliah SI2514024 Pemrograman Web. KampusLMS mengelola pengguna dan peran, mata kuliah, enrollment, materi, serta tugas dengan antarmuka web dan API v1 berautentikasi Sanctum.

## Teknologi

- PHP 8.2+
- Laravel 12
- SQLite secara default; MySQL/PostgreSQL juga dapat dipakai melalui konfigurasi Laravel
- Laravel Sanctum untuk autentikasi API

## Instalasi lokal

Jalankan dari direktori `lms-team-9`:

```sh
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
```

Di PowerShell, perintah salin yang setara adalah `Copy-Item .env.example .env`. Untuk frontend:

```sh
npm install
npm run build
php artisan serve
```

## Akun demo lokal

Seeder lokal menyediakan akun berikut. Password default `password` hanya untuk development/CI lokal; jangan gunakan password tersebut di server.

| Peran | Email |
|---|---|
| Admin | `admin@kampuslms.test` |
| Dosen | `dosen@kampuslms.test` |
| Mahasiswa | `mahasiswa@kampuslms.test` |

Di production, set `DEMO_ACCOUNT_PASSWORD` menjadi secret non-default sebelum menjalankan seeder. Seeder akan menolak password kosong atau `password`. Jangan menyimpan secret production di README, repository, atau `.env.example`.

## API

API v1 menggunakan prefix `/api/v1`. Login melalui `POST /api/v1/auth/login`, gunakan token Sanctum untuk route yang memerlukan autentikasi, dan baca profil saat ini melalui `GET /api/v1/me`. Rincian route dan contoh request/response: [`docs/api.md`](docs/api.md).

## Tim

| Anggota | Pembagian tugas |
|---|---|
| `[NAMA ANGGOTA 1]` | `[TUGAS]` |
| `[NAMA ANGGOTA 2]` | `[TUGAS]` |
| `[NAMA ANGGOTA 3]` | `[TUGAS]` |
| `[NAMA ANGGOTA 4]` | `[TUGAS]` |

## Aplikasi live

`[BELUM DIDEPLOY — isi tautan aplikasi setelah tersedia]`

## Antrean (Queue) & Notifikasi Asinkron (Milestone M3)

KampusLMS menggunakan antrean asinkron berbasis database (`QUEUE_CONNECTION=database`) untuk memproses pekerjaan berat di latar belakang, seperti pengiriman notifikasi publikasi tugas baru dan notifikasi penilaian.

### Menjalankan Queue Worker

Di terminal terpisah pada direktori `lms-team-9`, jalankan:

```sh
php artisan queue:work
```

> ⚠️ **PENTING (Kegagalan Senyap):** Jika `QUEUE_CONNECTION=database` tetapi queue worker tidak dijalankan, job akan menumpuk di tabel `jobs` dan notifikasi tidak akan terkirim tanpa ada pesan error. Pastikan worker selalu berjalan.

### Manajemen Job Gagal (Failed Jobs)

Jika job mengalami kegagalan setelah seluruh batas percobaan (`$tries = 3`, `$backoff = 10`) habis, kegagalan akan dicatat ke log dan disimpan di tabel `failed_jobs`.

- Memeriksa daftar job yang gagal:
  ```sh
  php artisan queue:failed
  ```
- Menjalankan ulang seluruh job yang gagal:
  ```sh
  php artisan queue:retry all
  ```

## Pengujian

```sh
php artisan test
```

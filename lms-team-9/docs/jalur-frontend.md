# Jalur Frontend KampusLMS

> **Status: Menunggu keputusan kelompok**

## Konteks

API KampusLMS sudah tersedia di `/api/v1` dengan autentikasi Sanctum Bearer Token. Keputusan ini menentukan bagaimana antarmuka pengguna akan dibangun untuk Minggu 7–16.

---

## Opsi yang Tersedia

### Opsi 1 — Blade + Alpine.js (atau Blade + Livewire)

**Deskripsi:** Tetap menggunakan server-side rendering Laravel Blade, dengan Alpine.js untuk interaktivitas ringan (atau Livewire untuk fitur reaktif tanpa JavaScript framework).

| Aspek | Detail |
|---|---|
| **Kelebihan** | Setup paling cepat, tidak perlu CORS, session auth bisa tetap dipakai, tim tidak perlu belajar framework JS baru |
| **Kekurangan** | UX kurang SPA-like, fitur real-time lebih sulit, tidak memanfaatkan API yang sudah dibuat |
| **Dampak ke API** | Sanctum SPA mode (cookie-based), CORS tidak diperlukan karena same-origin |
| **Cocok jika** | Tim lebih kuat di PHP/Laravel, waktu terbatas |

### Opsi 2 — Inertia.js + Vue 3 (atau React)

**Deskripsi:** Inertia bertindak sebagai "jembatan" antara Laravel controller dan Vue/React frontend. Tidak ada API JSON terpisah yang diekspos ke publik.

| Aspek | Detail |
|---|---|
| **Kelebihan** | SPA experience tanpa setup CORS, routing tetap di Laravel, auth via session, sangat terintegrasi |
| **Kekurangan** | Perlu belajar Inertia + Vue/React, lebih kompleks dari Blade murni |
| **Dampak ke API** | API publik (`/api/v1`) tetap ada untuk mobile/third-party; Inertia memakai route web |
| **Cocok jika** | Tim mau belajar Vue/React tapi tetap nyaman dengan Laravel controller |

### Opsi 3 — SPA Terpisah (Vue 3 / React + Axios/Fetch)

**Deskripsi:** Frontend dibangun terpisah (mis. Vite + Vue/React), mengonsumsi API `/api/v1` yang sudah dibuat.

| Aspek | Detail |
|---|---|
| **Kelebihan** | Pemisahan concern penuh, API sangat terpakai, bisa di-deploy terpisah |
| **Kekurangan** | CORS wajib dikonfigurasi, auth harus Bearer Token (bukan cookie), lebih banyak boilerplate |
| **Dampak ke API** | Perlu konfigurasi CORS di `config/cors.php`, Sanctum token-based (bukan SPA cookie) |
| **Cocok jika** | Tim punya kapasitas JS yang kuat, atau ada rencana mobile app |

---

## Konfigurasi Sanctum per Opsi

| Opsi | Auth Mode | CORS |
|---|---|---|
| Blade / Inertia | Cookie (SPA) | Tidak perlu |
| SPA Terpisah | Bearer Token | Perlu (`config/cors.php`) |

---

## Keputusan Kelompok

**Jalur yang dipilih kelompok:** ______________________

**Alasan:** ______________________

---

| Nama Anggota | Tanda Tangan | Tanggal |
|---|---|---|
| | | |
| | | |
| | | |

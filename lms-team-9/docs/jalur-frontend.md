# Jalur Frontend

Pemilihan jalur frontend untuk mengonsumsi API yang sudah dibuat.

## Opsi 1: Blade + Alpine.js / Livewire
Menggunakan framework Blade bawaan Laravel yang diperkaya dengan interaktivitas ringan menggunakan Alpine.js atau beralih sepenuhnya ke Livewire untuk komponen reaktif.
* **Kelebihan**: Mudah diimplementasi, tidak perlu setup SPA terpisah, terintegrasi penuh dengan ekosistem Laravel (tidak ada masalah CORS).
* **Kekurangan**: Beban di sisi server lebih tinggi karena merender HTML (Livewire), bisa terasa lambat jika UI sangat kompleks.
* **Dampak ke API**: Sebenarnya tidak memerlukan API Sanctum token base karena masih di session/cookie base, tapi bisa dikombinasikan jika dibutuhkan.

## Opsi 2: SPA (Single Page Application) - Vue / React (Inertia.js)
Menggunakan Inertia.js untuk menjembatani backend Laravel dan frontend Vue/React tanpa perlu membuat API endpoint khusus (menggunakan route web biasa namun merender komponen frontend).
* **Kelebihan**: Pengalaman SPA yang mulus, tidak perlu mengatur routing frontend manual, berbagi state auth dengan mudah (Sanctum SPA authentication cookie).
* **Kekurangan**: Setup awal butuh pembelajaran tambahan, struktur monolith (frontend dan backend terikat).
* **Dampak ke API**: Tidak membutuhkan Token (Bearer) Sanctum API melainkan Sanctum SPA Cookie untuk otentikasi.

## Opsi 3: SPA Terpisah (Vue/React/Next.js) + API (Bearer Token)
Frontend dibuat pada repository / instance terpisah dan melakukan fetch ke API Laravel KampusLMS yang ada.
* **Kelebihan**: Pemisahan tegas (decoupled) antara Frontend dan Backend, bisa dikembangkan oleh tim berbeda, API bisa digunakan oleh platform lain (Mobile).
* **Kekurangan**: Harus mengatur CORS, state auth dengan Token, kompleksitas deploy lebih tinggi.
* **Dampak ke API**: Akan 100% menggunakan API Sanctum Token yang kita buat minggu ini, membutuhkan konfigurasi CORS (di `config/cors.php`).

---
Jalur yang dipilih kelompok: ______

Alasan: ______

Nama Anggota:
Tanggal: 

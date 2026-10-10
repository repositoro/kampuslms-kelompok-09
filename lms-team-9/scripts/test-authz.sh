#!/usr/bin/env bash

# Menghentikan eksekusi skrip jika terjadi eror
set -e

# Menampilkan header pengujian
echo "=========================================="
echo " Running LMS Authorization & IDOR Tests "
echo "=========================================="

# 1. Menjalankan pengujian fitur otorisasi berbasis Laravel Test / PHPUnit
echo ""
echo "[1/3] Menjalankan Test Suite Otorisasi..."
php artisan test --filter=AuthorizationTest || php artisan test --filter=Auth

# 2. Memastikan seluruh route terdaftar dengan middleware otorisasi yang sesuai
echo ""
echo "[2/3] Memeriksa Daftar Route Terproteksi..."
php artisan route:list --path=dosen
php artisan route:list --path=mahasiswa
php artisan route:list --path=admin

# 3. Membersihkan cache konfigurasi & rute
echo ""
echo "[3/3] Membersihkan Cache Aplikasi..."
php artisan config:clear
php artisan route:clear

echo ""
echo "=========================================="
echo "   Pengujian Otorisasi Selesai (OK)     "
echo "=========================================="
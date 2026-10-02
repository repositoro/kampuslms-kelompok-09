#!/bin/bash

API_URL="http://localhost:8000/api/v1"

echo "=== Memulai Pengujian Otorisasi API ==="

# Helper untuk memformat output
check_status() {
    local name="$1"
    local expected="$2"
    local actual="$3"
    
    if [ "$expected" == "$actual" ]; then
        echo "PASS: $name (expected $expected, got $actual)"
    else
        echo "FAIL: $name (expected $expected, got $actual)"
    fi
}

echo -e "\n--- Test 1: Tanpa Token ---"
# 1. Request tanpa token (-> 401)
res=$(curl -s -o /dev/null -w "%{http_code}" -X GET "$API_URL/courses")
check_status "Akses /courses tanpa token" "401" "$res"

echo -e "\n--- Login ---"
# Silakan sesuaikan user/pass jika menggunakan seeder project aktual
MHS_TOKEN=$(curl -s -X POST "$API_URL/auth/login" -H "Content-Type: application/json" -d '{"email":"mahasiswa@test.com","password":"password"}' | grep -oP '"token":"\K[^"]+')
DOSEN_A_TOKEN=$(curl -s -X POST "$API_URL/auth/login" -H "Content-Type: application/json" -d '{"email":"dosena@test.com","password":"password"}' | grep -oP '"token":"\K[^"]+')
DOSEN_B_TOKEN=$(curl -s -X POST "$API_URL/auth/login" -H "Content-Type: application/json" -d '{"email":"dosenb@test.com","password":"password"}' | grep -oP '"token":"\K[^"]+')

echo -e "\n--- Test 2: Mahasiswa mengakses endpoint khusus Dosen ---"
res=$(curl -s -o /dev/null -w "%{http_code}" -X POST "$API_URL/assignments" \
  -H "Authorization: Bearer $MHS_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"title":"Test"}')
check_status "Mhs buat assignment" "403" "$res"

echo -e "\n--- Test 3: Dosen A mengakses data milik Dosen B ---"
# Asumsikan assignment 2 milik Dosen B
res=$(curl -s -o /dev/null -w "%{http_code}" -X PUT "$API_URL/assignments/2" \
  -H "Authorization: Bearer $DOSEN_A_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"title":"Hack"}')
check_status "Dosen A update assignment dosen B" "403" "$res"

echo -e "\n--- Test 4: User benar dengan hak yang benar ---"
# Asumsikan assignment 1 milik Dosen A
res=$(curl -s -o /dev/null -w "%{http_code}" -X PUT "$API_URL/assignments/1" \
  -H "Authorization: Bearer $DOSEN_A_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"title":"Valid Update"}')
check_status "Dosen A update assignment sendiri" "200" "$res"

echo -e "\n--- Test 5: Submission Mahasiswa (multipart) ---"
# Asumsikan Mhs terdaftar di course assignment 1
# Membuat dummy file
touch dummy.pdf
res=$(curl -s -o /dev/null -w "%{http_code}" -X POST "$API_URL/assignments/1/submissions" \
  -H "Authorization: Bearer $MHS_TOKEN" \
  -F "file=@dummy.pdf")
check_status "Mhs mengumpulkan tugas" "201" "$res"
rm dummy.pdf

echo -e "\n--- Test 6: Grade Upsert ---"
# Asumsikan submission id 1 milik assignment 1
res1=$(curl -s -o /dev/null -w "%{http_code}" -X PUT "$API_URL/submissions/1/grade" \
  -H "Authorization: Bearer $DOSEN_A_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"score": 90}')
check_status "Grade pertama kali" "201" "$res1"

res2=$(curl -s -o /dev/null -w "%{http_code}" -X PUT "$API_URL/submissions/1/grade" \
  -H "Authorization: Bearer $DOSEN_A_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"score": 95}')
check_status "Update grade (kedua kali)" "200" "$res2"

echo -e "\n--- Test 7: Rate Limit Login (5 req/min) ---"
# Lakukan spam login 6 kali
for i in {1..6}
do
   res=$(curl -s -o /dev/null -w "%{http_code}" -X POST "$API_URL/auth/login" -H "Content-Type: application/json" -d '{"email":"spam@test.com","password":"pass"}')
   if [ $i -eq 6 ]; then
      check_status "Spam login ke-6" "429" "$res"
   fi
done

echo -e "\n=== Pengujian Selesai ==="

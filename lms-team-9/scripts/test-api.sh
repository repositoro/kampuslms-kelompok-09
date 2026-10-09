#!/usr/bin/env bash
set -u

BASE_URL="http://localhost:8000/api/v1"
declare -i FAILURES=0
declare -i TOTAL=0

echo "Running API tests..."

assert_status() {
    local expected=$1
    local actual=$2
    local message=$3
    TOTAL+=1
    if [ "$actual" -eq "$expected" ]; then
        echo "PASS: $message (Expected $expected, Got $actual)"
    else
        echo "FAIL: $message (Expected $expected, Got $actual)"
        FAILURES+=1
    fi
}

echo "Seeding DB..."
php artisan migrate:fresh --seed > /dev/null

# Get tokens
TOKEN_ADMIN=$(curl -s -X POST $BASE_URL/auth/login -H "Accept: application/json" -H "Content-Type: application/json" -d '{"email":"admin@example.com", "password":"password"}' | grep -o '"token":"[^"]*' | grep -o '[^"]*$')
TOKEN_DOSEN_A=$(curl -s -X POST $BASE_URL/auth/login -H "Accept: application/json" -H "Content-Type: application/json" -d '{"email":"dosen_a@example.com", "password":"password"}' | grep -o '"token":"[^"]*' | grep -o '[^"]*$')
TOKEN_DOSEN_B=$(curl -s -X POST $BASE_URL/auth/login -H "Accept: application/json" -H "Content-Type: application/json" -d '{"email":"dosen_b@example.com", "password":"password"}' | grep -o '"token":"[^"]*' | grep -o '[^"]*$')
TOKEN_MHS_A=$(curl -s -X POST $BASE_URL/auth/login -H "Accept: application/json" -H "Content-Type: application/json" -d '{"email":"mahasiswa_a@example.com", "password":"password"}' | grep -o '"token":"[^"]*' | grep -o '[^"]*$')
TOKEN_MHS_B=$(curl -s -X POST $BASE_URL/auth/login -H "Accept: application/json" -H "Content-Type: application/json" -d '{"email":"mahasiswa_b@example.com", "password":"password"}' | grep -o '"token":"[^"]*' | grep -o '[^"]*$')

echo "Admin Token: $TOKEN_ADMIN"
echo "Dosen A Token: $TOKEN_DOSEN_A"

# Tanpa token -> 401
status_code=$(curl -s -o /dev/null -w "%{http_code}" -X GET $BASE_URL/courses -H "Accept: application/json")
assert_status 401 $status_code "Tanpa token -> 401"

# Mahasiswa mengakses endpoint khusus dosen/admin -> 403
status_code=$(curl -s -o /dev/null -w "%{http_code}" -X POST $BASE_URL/courses -H "Accept: application/json" -H "Authorization: Bearer $TOKEN_MHS_A" -H "Content-Type: application/json" -d '{"title":"Test", "description":"Test"}')
assert_status 403 $status_code "Mahasiswa membuat course -> 403"

# Dosen B mengubah Course milik Dosen A -> 403 (Assuming course 1 belongs to Dosen A)
status_code=$(curl -s -o /dev/null -w "%{http_code}" -X PUT $BASE_URL/courses/1 -H "Accept: application/json" -H "Authorization: Bearer $TOKEN_DOSEN_B" -H "Content-Type: application/json" -d '{"title":"Hacked"}')
assert_status 403 $status_code "Dosen B edit course Dosen A -> 403"

# Mahasiswa B membaca Submission milik Mahasiswa A -> 403 (Assuming submission 1 belongs to Mhs A)
status_code=$(curl -s -o /dev/null -w "%{http_code}" -X GET $BASE_URL/submissions/1 -H "Accept: application/json" -H "Authorization: Bearer $TOKEN_MHS_B")
assert_status 403 $status_code "Mahasiswa B baca submission Mahasiswa A -> 403"

# Nested dengan parent salah -> 404 (scoped binding)
# Assuming material 1 is NOT in course 99
status_code=$(curl -s -o /dev/null -w "%{http_code}" -X GET $BASE_URL/courses/99/materials -H "Accept: application/json" -H "Authorization: Bearer $TOKEN_MHS_A")
assert_status 404 $status_code "Nested salah -> 404"

# Login salah 6x
for i in {1..5}; do
    curl -s -o /dev/null -w "%{http_code}" -X POST $BASE_URL/auth/login -H "Accept: application/json" -H "Content-Type: application/json" -d '{"email":"bad@example.com", "password":"wrong"}'
done
status_code=$(curl -s -o /dev/null -w "%{http_code}" -X POST $BASE_URL/auth/login -H "Accept: application/json" -H "Content-Type: application/json" -d '{"email":"bad@example.com", "password":"wrong"}')
assert_status 429 $status_code "Login salah 6x -> 429"

echo ""
echo "Test Finished. Total: $TOTAL. Failures: $FAILURES."
if [ "$FAILURES" -gt 0 ]; then
    exit 1
fi
exit 0

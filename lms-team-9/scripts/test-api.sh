#!/usr/bin/env bash
# scripts/test-api.sh — Pengujian otorisasi API KampusLMS (Prompt C)
# Gunakan: chmod +x scripts/test-api.sh && bash scripts/test-api.sh
set -u

BASE_URL="${BASE_URL:-http://localhost:8000/api/v1}"
PASS=0
FAIL=0

pass() { echo "  [PASS] $1"; PASS=$((PASS+1)); }
fail() { echo "  [FAIL] $1 (expected: $2, got: $3)"; FAIL=$((FAIL+1)); }

check() {
    local desc="$1" expected="$2" actual="$3"
    if [ "$actual" = "$expected" ]; then pass "$desc"; else fail "$desc" "$expected" "$actual"; fi
}

# Ambil token
get_token() {
    curl -s -X POST "$BASE_URL/auth/login" \
        -H "Accept: application/json" \
        -H "Content-Type: application/json" \
        -d "{\"email\":\"$1\",\"password\":\"$2\"}" \
        | grep -o '"token":"[^"]*"' | sed 's/"token":"//;s/"//'
}

# HTTP status code saja
http_status() {
    curl -s -o /dev/null -w "%{http_code}" \
        -H "Accept: application/json" \
        -H "Authorization: Bearer $1" \
        "$2"
}

echo ""
echo "=== KampusLMS API Authorization Test ==="
echo ""

# --- Ambil token semua peran ---
echo "» Login dan mengambil token..."
TOKEN_ADMIN=$(get_token "admin@test.com" "password")
TOKEN_DOSEN_A=$(get_token "dosena@test.com" "password")
TOKEN_DOSEN_B=$(get_token "dosenb@test.com" "password")
TOKEN_MHS_A=$(get_token "mhsa@test.com" "password")
TOKEN_MHS_B=$(get_token "mhsb@test.com" "password")

[ -n "$TOKEN_ADMIN" ] && pass "Login admin" || { fail "Login admin" "token" "kosong"; }
[ -n "$TOKEN_DOSEN_A" ] && pass "Login dosen A" || { fail "Login dosen A" "token" "kosong"; }
[ -n "$TOKEN_DOSEN_B" ] && pass "Login dosen B" || { fail "Login dosen B" "token" "kosong"; }
[ -n "$TOKEN_MHS_A" ] && pass "Login mahasiswa A" || { fail "Login mahasiswa A" "token" "kosong"; }
[ -n "$TOKEN_MHS_B" ] && pass "Login mahasiswa B" || { fail "Login mahasiswa B" "token" "kosong"; }

echo ""
echo "--- [A] Authentication ---"
# Tanpa token → 401
STATUS=$(curl -s -o /dev/null -w "%{http_code}" -H "Accept: application/json" "$BASE_URL/auth/me")
check "Tanpa token → 401" "401" "$STATUS"

echo ""
echo "--- [B] Role Authorization ---"
# Admin ke route admin → 200
STATUS=$(http_status "$TOKEN_ADMIN" "$BASE_URL/admin/courses")
check "Admin akses /admin/courses → 200" "200" "$STATUS"

# Mahasiswa ke route admin → 403
STATUS=$(http_status "$TOKEN_MHS_A" "$BASE_URL/admin/courses")
check "Mahasiswa akses /admin/courses → 403" "403" "$STATUS"

echo ""
echo "--- [C] IDOR Ownership ---"
# Ambil ID course dan assignment (asumsi seeder sudah dijalankan)
COURSE_ID=$(curl -s -H "Accept: application/json" -H "Authorization: Bearer $TOKEN_DOSEN_A" \
    "$BASE_URL/courses" | grep -o '"id":[0-9]*' | head -1 | sed 's/"id"://')
ASSIGNMENT_ID=$(curl -s -H "Accept: application/json" -H "Authorization: Bearer $TOKEN_DOSEN_A" \
    "$BASE_URL/courses/$COURSE_ID/assignments" | grep -o '"id":[0-9]*' | head -1 | sed 's/"id"://')
SUBMISSION_ID=$(curl -s -H "Accept: application/json" -H "Authorization: Bearer $TOKEN_DOSEN_A" \
    "$BASE_URL/assignments/$ASSIGNMENT_ID/submissions" | grep -o '"id":[0-9]*' | head -1 | sed 's/"id"://')

# Dosen B mengubah Assignment milik Dosen A → 403
if [ -n "$ASSIGNMENT_ID" ]; then
    STATUS=$(curl -s -o /dev/null -w "%{http_code}" -X PUT \
        -H "Accept: application/json" -H "Content-Type: application/json" \
        -H "Authorization: Bearer $TOKEN_DOSEN_B" \
        -d '{"title":"Curi Tugas"}' \
        "$BASE_URL/assignments/$ASSIGNMENT_ID")
    check "Dosen B ubah Assignment milik Dosen A → 403" "403" "$STATUS"
else
    fail "Dosen B ubah Assignment milik Dosen A → 403" "403" "SKIP (assignment_id kosong)"
fi

# Mahasiswa B baca Submission milik Mahasiswa A → 403
if [ -n "$SUBMISSION_ID" ]; then
    STATUS=$(http_status "$TOKEN_MHS_B" "$BASE_URL/submissions/$SUBMISSION_ID")
    check "Mahasiswa B baca Submission milik A → 403" "403" "$STATUS"
else
    fail "Mahasiswa B baca Submission milik A → 403" "403" "SKIP (submission_id kosong)"
fi

echo ""
echo "--- [D] Nested Binding (scopeBindings) ---"
# Material via course yang salah → 404
if [ -n "$ASSIGNMENT_ID" ]; then
    WRONG_COURSE_ID=99999
    STATUS=$(http_status "$TOKEN_DOSEN_A" "$BASE_URL/courses/$WRONG_COURSE_ID/materials")
    check "Material via course tidak ada → 404" "404" "$STATUS"
fi

echo ""
echo "--- [E] Rate Limit Login ---"
echo "  (Mengirim 6 request login salah...)"
for i in 1 2 3 4 5; do
    curl -s -o /dev/null -X POST "$BASE_URL/auth/login" \
        -H "Accept: application/json" -H "Content-Type: application/json" \
        -d '{"email":"dosena@test.com","password":"salah"}' > /dev/null
done
STATUS=$(curl -s -o /dev/null -w "%{http_code}" -X POST "$BASE_URL/auth/login" \
    -H "Accept: application/json" -H "Content-Type: application/json" \
    -d '{"email":"dosena@test.com","password":"salah"}')
check "Login ke-6 (salah berturut-turut) → 429" "429" "$STATUS"

echo ""
echo "=================================================="
echo "  Total: $((PASS+FAIL)) | PASS: $PASS | FAIL: $FAIL"
echo "=================================================="
[ "$FAIL" -eq 0 ] && exit 0 || exit 1

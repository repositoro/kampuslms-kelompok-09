<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Periksa apakah user yang sedang login memiliki salah satu peran yang diizinkan.
     *
     * Penggunaan di route: middleware('role:admin') atau middleware('role:admin,dosen')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        // 1. Jika belum login (401 Unauthorized)
        if (! $user) {
            abort(401, 'Silakan login terlebih dahulu untuk mengakses halaman ini.');
        }

        // 2. Jika sudah login tapi peran tidak sesuai (403 Forbidden)
        if (! in_array($user->role, $roles, true)) {
            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk melihat halaman ini.');
        }

        return $next($request);
    }
}

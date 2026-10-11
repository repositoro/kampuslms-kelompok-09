<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View; // 1. Import Facade Gate

class UserController extends Controller
{
    /**
     * Tampilkan daftar seluruh pengguna dengan pencarian dan filter.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', User::class); // 2. Otorisasi melihat daftar pengguna

        $query = User::latest();

        // Filter pencarian berdasarkan nama atau email
        if ($request->filled('search')) {
            $cari = $request->search;
            $query->where(function ($q) use ($cari) {
                $q->where('name', 'like', '%'.$cari.'%')
                    ->orWhere('email', 'like', '%'.$cari.'%');
            });
        }

        // Filter role (admin / dosen / mahasiswa)
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Paginate 15 per halaman, pertahankan query string (search & role)
        $users = $query->paginate(15)->withQueryString();

        return view('users.index', compact('users'));
    }

    /**
     * Tampilkan formulir tambah pengguna.
     */
    public function create(): View
    {
        Gate::authorize('create', User::class); // 3. Otorisasi buka form tambah

        return view('users.create');
    }

    /**
     * Simpan pengguna baru ke database.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        Gate::authorize('create', User::class); // 4. Otorisasi simpan data baru

        $data = $request->validated();

        $user = new User;
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->nim_nip = $data['nim_nip'] ?? null;
        $user->password = bcrypt($data['password']);
        $user->role = $data['role']; // Eksplisit — role tidak ada di fillable
        $user->save();

        return redirect()->route('admin.users.index')
            ->with('success', 'Pengguna berhasil ditambahkan!');
    }

    /**
     * Tampilkan detail pengguna.
     */
    public function show(User $user): View
    {
        Gate::authorize('view', $user); // 5. Otorisasi lihat detail pengguna spesifik

        return view('users.show', compact('user'));
    }

    /**
     * Tampilkan formulir edit pengguna.
     */
    public function edit(User $user): View
    {
        Gate::authorize('update', $user); // 6. Otorisasi buka form edit

        return view('users.edit', compact('user'));
    }

    /**
     * Perbarui data pengguna di database.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        Gate::authorize('update', $user); // 7. Otorisasi simpan pembaruan data

        $data = $request->validated();

        if ($user->taughtCourses()->exists() && $data['role'] !== 'dosen') {
            return back()->withErrors([
                'role' => 'Pindahkan mata kuliah yang diampu sebelum mengubah peran dosen.',
            ])->withInput();
        }

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->role = $data['role']; // Eksplisit — role tidak ada di fillable
        if (array_key_exists('nim_nip', $data)) {
            $user->nim_nip = $data['nim_nip'];
        }

        // Update password hanya jika diisi (nullable di Form Request)
        if (! empty($data['password'])) {
            $user->password = bcrypt($data['password']);
        }

        $user->save();

        return redirect()->route('admin.users.index')
            ->with('success', 'Pengguna berhasil diperbarui!');
    }

    /**
     * Hapus pengguna dari database.
     */
    public function destroy(User $user)
    {
        Gate::authorize('delete', $user); // 8. Otorisasi hapus data

        if ($user->taughtCourses()->exists()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Pindahkan mata kuliah yang diampu sebelum menghapus pengguna.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Pengguna berhasil dihapus.');
    }
}

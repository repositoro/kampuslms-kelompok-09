<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Tampilkan daftar seluruh pengguna dengan pencarian dan filter.
     */
    public function index(Request $request): View
    {
        $query = User::latest();

        if ($request->filled('search')) {
            $cari = $request->search;
            $query->where(function ($q) use ($cari) {
                $q->where('name', 'like', '%' . $cari . '%')
                  ->orWhere('email', 'like', '%' . $cari . '%');
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    /**
     * Tampilkan formulir tambah pengguna.
     */
    public function create(): View
    {
        return view('admin.users.create');
    }

    /**
     * Simpan pengguna baru ke database.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $user           = new User();
        $user->name     = $data['name'];
        $user->email    = $data['email'];
        $user->password = bcrypt($data['password']);
        $user->role     = $data['role']; // Eksplisit — role tidak ada di fillable
        $user->save();

        return redirect()->route('admin.users.index')
            ->with('success', 'Pengguna berhasil ditambahkan!');
    }

    /**
     * Tampilkan detail pengguna.
     */
    public function show(User $user): View
    {
        return view('admin.users.show', compact('user'));
    }

    /**
     * Tampilkan formulir edit pengguna.
     */
    public function edit(User $user): View
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Perbarui data pengguna di database.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        $user->name  = $data['name'];
        $user->email = $data['email'];
        $user->role  = $data['role']; // Eksplisit — role tidak ada di fillable

        if (!empty($data['password'])) {
            $user->password = bcrypt($data['password']);
        }

        $user->save();

        return redirect()->route('admin.users.index')
            ->with('success', 'Pengguna berhasil diperbarui!');
    }

    /**
     * Hapus pengguna dari database.
     */
    public function destroy(User $user): RedirectResponse
    {
        try {
            $user->delete();
            return redirect()->route('admin.users.index')
                ->with('success', 'Pengguna berhasil dihapus.');
        } catch (QueryException $e) {
            if ($e->getCode() == '23000') {
                return redirect()->route('admin.users.index')
                    ->with('error', 'Dosen tidak dapat dihapus karena masih terikat dengan data mata kuliah.');
            }

            return redirect()->route('admin.users.index')
                ->with('error', 'Terjadi kesalahan saat menghapus pengguna.');
        }
    }
}

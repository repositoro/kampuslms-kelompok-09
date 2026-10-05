<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Ambil ID pengguna dari route model binding
        $userId = $this->route('user')->id;

        return [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,' . $userId,
            'password' => 'nullable|string|min:8',
            'role'     => 'required|in:admin,dosen,mahasiswa',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'  => 'Nama lengkap wajib diisi.',
            'name.max'       => 'Nama lengkap maksimal 255 karakter.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email'    => 'Format alamat email tidak valid.',
            'email.unique'   => 'Alamat email sudah digunakan oleh pengguna lain.',
            'password.min'   => 'Kata sandi minimal 8 karakter.',
            'role.required'  => 'Peran pengguna wajib dipilih.',
            'role.in'        => 'Peran tidak valid. Pilih salah satu: Admin, Dosen, atau Mahasiswa.',
        ];
    }
}

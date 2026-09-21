<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna memiliki otorisasi untuk melakukan permintaan ini.
     */
    public function authorize(): bool
    {
        return true; // TODO: Di minggu 7 diganti dengan otorisasi Policy
    }

    /**
     * Aturan validasi yang berlaku untuk request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role'     => ['required', 'in:admin,dosen,mahasiswa'],
            'nim_nip'  => ['nullable', 'string', 'max:50', 'unique:users,nim_nip'],
        ];
    }

    /**
     * Pesan validasi kustom dalam bahasa Indonesia.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required'     => 'Nama pengguna wajib diisi.',
            'name.max'          => 'Nama pengguna maksimal 255 karakter.',
            'email.required'    => 'Alamat email wajib diisi.',
            'email.email'       => 'Format alamat email tidak valid.',
            'email.unique'      => 'Alamat email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min'      => 'Password minimal terdiri dari 8 karakter.',
            'role.required'     => 'Role pengguna wajib dipilih.',
            'role.in'           => 'Role harus salah satu dari: admin, dosen, atau mahasiswa.',
            'nim_nip.unique'    => 'NIM / NIP sudah digunakan oleh pengguna lain.',
            'nim_nip.max'       => 'NIM / NIP maksimal 50 karakter.',
        ];
    }
}

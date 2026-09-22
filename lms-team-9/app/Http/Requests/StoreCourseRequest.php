<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code'        => 'required|string|max:20|unique:courses,code',
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'sks'         => 'required|integer|min:1|max:6',
            'lecturer_id' => 'required|exists:users,id',
            'status'      => 'required|in:draft,active,archived',
        ];
    }

    public function messages(): array
    {
        return [
            'code.required'        => 'Kode mata kuliah wajib diisi.',
            'code.max'             => 'Kode mata kuliah maksimal 20 karakter.',
            'code.unique'          => 'Kode mata kuliah sudah digunakan, silakan gunakan kode lain.',
            'name.required'        => 'Nama mata kuliah wajib diisi.',
            'name.max'             => 'Nama mata kuliah maksimal 255 karakter.',
            'sks.required'         => 'Jumlah SKS wajib diisi.',
            'sks.integer'          => 'Jumlah SKS harus berupa angka bulat.',
            'sks.min'              => 'Jumlah SKS minimal 1.',
            'sks.max'              => 'Jumlah SKS maksimal 6.',
            'lecturer_id.required' => 'Dosen pengampu wajib dipilih.',
            'lecturer_id.exists'   => 'Dosen yang dipilih tidak ditemukan dalam sistem.',
            'status.required'      => 'Status mata kuliah wajib dipilih.',
            'status.in'            => 'Status tidak valid. Pilih salah satu: Draft, Aktif, atau Diarsipkan.',
        ];
    }
}

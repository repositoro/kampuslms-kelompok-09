<?php

namespace App\Http\Requests;

use App\Models\Material;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreMaterialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('create', [Material::class, $this->route('course')]);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', Rule::in(['file', 'link'])],
            'file' => [
                Rule::requiredIf(fn () => $this->input('type') === 'file'),
                'nullable',
                'file',
                'mimes:pdf,pptx,ppt,docx,doc',
                'extensions:pdf,pptx,ppt,docx,doc',
                'max:20480', // 20 MB (20480 KB)
            ],
            'external_url' => [
                Rule::requiredIf(fn () => $this->input('type') === 'link'),
                'nullable',
                'url',
                'regex:/\Ahttps?:\/\//i',
                'max:2048',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Judul materi wajib diisi.',
            'type.required' => 'Pilih jenis materi (berkas atau tautan).',
            'file.required_if' => 'Berkas materi wajib diunggah jika memilih opsi berkas.',
            'file.mimes' => 'Format berkas harus berupa PDF, PPT, PPTX, DOC, atau DOCX.',
            'file.max' => 'Ukuran berkas maksimal adalah 20 MB.',
            'external_url.required_if' => 'Tautan eksternal wajib diisi jika memilih opsi tautan.',
            'external_url.url' => 'Format tautan eksternal tidak valid.',
        ];
    }
}
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
                'file',
                'mimes:pdf,pptx',
                'extensions:pdf,pptx',
                'max:10240',
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
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateMaterialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('update', $this->route('material'));
    }

    public function rules(): array
    {
        $material = $this->route('material');

        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'type' => ['sometimes', 'required', Rule::in(['file', 'link'])],
            'file' => [
                Rule::requiredIf(fn () => $this->input('type', $material->type) === 'file'
                    && ($material->type !== 'file' || ! $material->file_path)),
                'file',
                'mimes:pdf,pptx',
                'extensions:pdf,pptx',
                'max:10240',
            ],
            'external_url' => [
                Rule::requiredIf(fn () => $this->input('type') === 'link'),
                'nullable',
                'url',
                'max:2048',
            ],
        ];
    }
}

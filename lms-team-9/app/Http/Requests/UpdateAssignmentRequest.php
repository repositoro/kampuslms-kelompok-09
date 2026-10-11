<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdateAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('update', $this->route('assignment'));
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'instructions' => ['sometimes', 'required', 'string'],
            'due_at' => ['sometimes', 'required', 'date'],
            'max_score' => ['sometimes', 'integer', 'between:1,255'],
            'allow_late' => ['sometimes', 'boolean'],
            'status' => ['sometimes', 'required', 'in:draft,published'],
        ];
    }
}

<?php

namespace App\Http\Requests;

use App\Models\Assignment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class StoreAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('create', [Assignment::class, $this->route('course')]);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'instructions' => ['required', 'string'],
            'due_at' => ['required', 'date'],
            'max_score' => ['sometimes', 'integer', 'between:1,255'],
            'allow_late' => ['sometimes', 'boolean'],
            'status' => ['required', 'in:draft,published'],
        ];
    }
}

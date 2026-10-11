<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $courseId = $this->route('course')->id;

        return [
            'code' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('courses', 'code')->ignore($courseId)],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'sks' => ['sometimes', 'required', 'integer', 'between:1,255'],
            'lecturer_id' => ['sometimes', 'required', Rule::exists('users', 'id')->where('role', 'dosen')],
            'status' => ['sometimes', 'required', Rule::in(['draft', 'active', 'archived'])],
        ];
    }
}

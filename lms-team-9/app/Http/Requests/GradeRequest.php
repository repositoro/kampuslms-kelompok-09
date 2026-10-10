<?php

namespace App\Http\Requests;

use App\Models\Grade;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class GradeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('create', [Grade::class, $this->route('submission')]);
    }

    public function rules(): array
    {
        $submission = $this->route('submission');

        return [
            'score' => ['required', 'numeric', 'min:0', 'max:'.$submission->assignment->max_score],
            'feedback' => ['nullable', 'string'],
        ];
    }
}

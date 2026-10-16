<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class UpdateGradeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $submission = $this->route('submission');
        return $this->user()->role === 'admin'
        || ($this->user()->role === 'dosen' && $submission->assignment->course->lecturer_id === $this->user()->id);
    }

    public function rules(): array
    {
        $maxScore = $this->route('submission')->assignment->max_score ?? 100;

        return [
            'score' => ['required', 'numeric', 'min:0', 'max:' . $maxScore],
            'feedback' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
   
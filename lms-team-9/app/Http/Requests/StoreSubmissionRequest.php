<?php

namespace App\Http\Requests;

use App\Models\Submission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Validator;

class StoreSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('create', [Submission::class, $this->route('assignment')]);
    }

    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'mimes:pdf,doc,docx,zip,rar',
                'extensions:pdf,doc,docx,zip,rar',
                'max:20480', // 20 MB (20480 KB)
            ],
            'note' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $assignment = $this->route('assignment');
            $user = $this->user();

            if (! $assignment->allow_late && now()->greaterThan($assignment->due_at)) {
                $validator->errors()->add('file', 'Batas waktu pengumpulan telah lewat.');
            }

            if ($assignment->submissions()
                ->where('user_id', $user->id)
                ->whereHas('grade')
                ->exists()) {
                $validator->errors()->add('file', 'Tugas yang sudah dinilai tidak dapat dikumpulkan ulang.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Berkas tugas wajib diunggah.',
            'file.mimes' => 'Format berkas harus berupa PDF, DOC, DOCX, ZIP, atau RAR.',
            'file.extensions' => 'Ekstensi berkas tidak valid, gunakan format PDF, DOC, DOCX, ZIP, atau RAR.',
            'file.max' => 'Ukuran berkas maksimal adalah 20 MB.',
            'note.max' => 'Catatan maksimal 5000 karakter.',
        ];
    }
}

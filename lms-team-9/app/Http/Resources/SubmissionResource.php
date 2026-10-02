<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubmissionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'assignment_id' => $this->assignment_id,
            'file_url' => $this->file_path ? asset('storage/' . $this->file_path) : null,
            'original_name' => $this->original_name,
            'note' => $this->note,
            'is_late' => $this->is_late,
            'submitted_at' => $this->submitted_at,
            'student' => new UserResource($this->whenLoaded('student')),
            'grade' => new GradeResource($this->whenLoaded('grade')),
            'created_at' => $this->created_at,
        ];
    }
}

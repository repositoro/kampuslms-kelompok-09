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
            'user_id' => $this->user_id,
            'file_url' => route('api.v1.submissions.download', $this->resource, false),
            'original_name' => $this->original_name,
            'file_size' => $this->file_size,
            'note' => $this->note,
            'submitted_at' => $this->submitted_at?->toIso8601String(),
            'is_late' => $this->is_late,
            'user' => new UserResource($this->whenLoaded('student')),
            'grade' => new GradeResource($this->whenLoaded('grade')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

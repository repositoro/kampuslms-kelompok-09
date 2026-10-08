<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaterialResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'course_id'     => $this->course_id,
            'title'         => $this->title,
            'description'   => $this->description,
            'type'          => $this->type,
            'file_path'     => $this->type === 'file' ? $this->file_path : null,
            'original_name' => $this->original_name,
            'file_size'     => $this->file_size,
            'mime_type'     => $this->mime_type,
            'external_url'  => $this->type === 'link' ? $this->external_url : null,
            'uploader'      => new UserResource($this->whenLoaded('uploader')),
            'course'        => new CourseResource($this->whenLoaded('course')),
            'created_at'    => $this->created_at?->toISOString(),
            'updated_at'    => $this->updated_at?->toISOString(),
        ];
    }
}

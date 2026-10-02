<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaterialResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'file_url' => $this->file_path ? asset('storage/' . $this->file_path) : null,
            'external_url' => $this->external_url,
            'uploader' => new UserResource($this->whenLoaded('uploader')),
            'created_at' => $this->created_at,
        ];
    }
}

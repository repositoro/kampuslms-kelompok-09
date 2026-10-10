<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMaterialRequest;
use App\Http\Requests\UpdateMaterialRequest;
use App\Http\Resources\MaterialResource;
use App\Models\Course;
use App\Models\Material;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class MaterialController extends Controller
{
    public function index(Course $course)
    {
        Gate::authorize('view', $course);
        $materials = $course->materials()->paginate();

        return MaterialResource::collection($materials);
    }

    public function store(StoreMaterialRequest $request, Course $course)
    {
        $data = $request->safe()->except('file');
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $data['type'] = 'file';
            $data['file_path'] = $file->store('materials', 'local');
            $data['original_name'] = basename($file->getClientOriginalName());
            $data['file_size'] = $file->getSize();
            $data['mime_type'] = $file->getMimeType();
        }
        $data['uploaded_by'] = $request->user()->id;
        $material = $course->materials()->create($data);

        return new MaterialResource($material);
    }

    public function show(Material $material)
    {
        Gate::authorize('view', $material);

        return new MaterialResource($material);
    }

    public function update(UpdateMaterialRequest $request, Material $material)
    {
        $data = $request->safe()->except('file');
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $data['type'] = 'file';
            $data['file_path'] = $file->store('materials', 'local');
            $data['original_name'] = basename($file->getClientOriginalName());
            $data['file_size'] = $file->getSize();
            $data['mime_type'] = $file->getMimeType();
        }
        if (($data['type'] ?? $material->type) === 'link') {
            $data['file_path'] = null;
            $data['original_name'] = null;
            $data['file_size'] = null;
            $data['mime_type'] = null;
        }
        $oldFilePath = $material->file_path;
        $material->update($data);
        if ($oldFilePath && $material->file_path !== $oldFilePath) {
            Storage::disk('local')->delete($oldFilePath);
        }

        return new MaterialResource($material);
    }

    public function destroy(Material $material)
    {
        Gate::authorize('delete', $material);

        if ($material->file_path) {
            Storage::disk('local')->delete($material->file_path);
        }
        $material->delete();

        return response()->json(null, 204);
    }
}

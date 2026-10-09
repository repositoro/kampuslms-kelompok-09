<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Material;
use App\Http\Resources\MaterialResource;
use App\Http\Requests\StoreMaterialRequest;
use App\Http\Requests\UpdateMaterialRequest;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    public function index(Course $course)
    {
        $materials = $course->materials()->paginate();
        return MaterialResource::collection($materials);
    }

    public function store(StoreMaterialRequest $request, Course $course)
    {
        abort_unless($request->user()->role === 'dosen' && $course->lecturer_id === $request->user()->id, 403, 'This action is unauthorized.');
        
        $material = $course->materials()->create($request->validated());
        return new MaterialResource($material);
    }

    public function show(Material $material)
    {
        return new MaterialResource($material);
    }

    public function update(UpdateMaterialRequest $request, Material $material)
    {
        $course = $material->course;
        abort_unless($request->user()->role === 'dosen' && $course->lecturer_id === $request->user()->id, 403, 'This action is unauthorized.');
        
        $material->update($request->validated());
        return new MaterialResource($material);
    }

    public function destroy(Request $request, Material $material)
    {
        $course = $material->course;
        abort_unless($request->user()->role === 'dosen' && $course->lecturer_id === $request->user()->id, 403, 'This action is unauthorized.');
        
        $material->delete();
        return response()->json(null, 204);
    }
}
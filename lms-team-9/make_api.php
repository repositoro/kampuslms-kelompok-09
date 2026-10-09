<?php

$dir = __DIR__;

$controllers = [
    'AuthController' => <<<'PHP'
<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => [
                    'email' => ['The provided credentials do not match our records.']
                ]
            ], 422);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ]
            ]
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(null, 204);
    }

    public function me(Request $request)
    {
        return response()->json([
            'data' => [
                'id' => $request->user()->id,
                'name' => $request->user()->name,
                'email' => $request->user()->email,
                'role' => $request->user()->role,
            ]
        ]);
    }
}
PHP,

    'CourseController' => <<<'PHP'
<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Http\Resources\CourseResource;
use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $courses = Course::with('lecturer')->withCount('materials')->paginate();
        return CourseResource::collection($courses);
    }

    public function store(StoreCourseRequest $request)
    {
        abort_unless($request->user()->role === 'admin' || $request->user()->role === 'dosen', 403, 'This action is unauthorized.');
        
        // TODO(minggu 7): pindahkan ke Policy
        $data = $request->validated();
        if ($request->user()->role === 'dosen') {
            $data['lecturer_id'] = $request->user()->id;
        }

        $course = Course::create($data);
        return new CourseResource($course);
    }

    public function show(Course $course)
    {
        $course->load('lecturer');
        return new CourseResource($course);
    }

    public function update(UpdateCourseRequest $request, Course $course)
    {
        // TODO(minggu 7): pindahkan ke Policy
        abort_unless($request->user()->role === 'admin' || ($request->user()->role === 'dosen' && $course->lecturer_id === $request->user()->id), 403, 'This action is unauthorized.');
        
        $course->update($request->validated());
        return new CourseResource($course);
    }

    public function destroy(Request $request, Course $course)
    {
        // TODO(minggu 7): pindahkan ke Policy
        abort_unless($request->user()->role === 'admin' || ($request->user()->role === 'dosen' && $course->lecturer_id === $request->user()->id), 403, 'This action is unauthorized.');
        
        $course->delete();
        return response()->json(null, 204);
    }
}
PHP,
    
    'MaterialController' => <<<'PHP'
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
PHP,

    'AssignmentController' => <<<'PHP'
<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Assignment;
use App\Http\Resources\AssignmentResource;
use App\Http\Requests\StoreAssignmentRequest;
use App\Http\Requests\UpdateAssignmentRequest;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    public function index(Course $course)
    {
        $assignments = $course->assignments()->paginate();
        return AssignmentResource::collection($assignments);
    }

    public function store(StoreAssignmentRequest $request, Course $course)
    {
        abort_unless($request->user()->role === 'dosen' && $course->lecturer_id === $request->user()->id, 403, 'This action is unauthorized.');
        
        $assignment = $course->assignments()->create($request->validated());
        return new AssignmentResource($assignment);
    }

    public function show(Assignment $assignment)
    {
        return new AssignmentResource($assignment);
    }

    public function update(UpdateAssignmentRequest $request, Assignment $assignment)
    {
        $course = $assignment->course;
        abort_unless($request->user()->role === 'dosen' && $course->lecturer_id === $request->user()->id, 403, 'This action is unauthorized.');
        
        $assignment->update($request->validated());
        return new AssignmentResource($assignment);
    }

    public function destroy(Request $request, Assignment $assignment)
    {
        $course = $assignment->course;
        abort_unless($request->user()->role === 'dosen' && $course->lecturer_id === $request->user()->id, 403, 'This action is unauthorized.');
        
        $assignment->delete();
        return response()->json(null, 204);
    }
}
PHP,

    'SubmissionController' => <<<'PHP'
<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Submission;
use App\Http\Resources\SubmissionResource;
use App\Http\Requests\StoreSubmissionRequest;
use Illuminate\Http\Request;

class SubmissionController extends Controller
{
    public function index(Assignment $assignment)
    {
        $submissions = $assignment->submissions()->with(['user', 'grade'])->paginate();
        return SubmissionResource::collection($submissions);
    }

    public function store(StoreSubmissionRequest $request, Assignment $assignment)
    {
        abort_unless($request->user()->role === 'mahasiswa', 403, 'This action is unauthorized.');
        
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
        $submission = $assignment->submissions()->create($data);
        return new SubmissionResource($submission);
    }

    public function show(Request $request, Submission $submission)
    {
        $course = $submission->assignment->course;
        abort_unless(
            $request->user()->role === 'admin' || 
            ($request->user()->role === 'dosen' && $course->lecturer_id === $request->user()->id) ||
            ($request->user()->role === 'mahasiswa' && $submission->user_id === $request->user()->id),
            403, 'This action is unauthorized.'
        );
        $submission->load(['user', 'grade']);
        return new SubmissionResource($submission);
    }
}
PHP,

    'GradeController' => <<<'PHP'
<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use App\Models\Grade;
use App\Http\Resources\GradeResource;
use App\Http\Requests\StoreGradeRequest;
use App\Http\Requests\UpdateGradeRequest;
use Illuminate\Http\Request;

class GradeController extends Controller
{
    public function store(StoreGradeRequest $request, Submission $submission)
    {
        $course = $submission->assignment->course;
        abort_unless($request->user()->role === 'dosen' && $course->lecturer_id === $request->user()->id, 403, 'This action is unauthorized.');
        
        $data = $request->validated();
        $data['graded_by'] = $request->user()->id;
        $grade = $submission->grade()->create($data);
        return new GradeResource($grade);
    }

    public function update(UpdateGradeRequest $request, Submission $submission)
    {
        $course = $submission->assignment->course;
        abort_unless($request->user()->role === 'dosen' && $course->lecturer_id === $request->user()->id, 403, 'This action is unauthorized.');
        
        $grade = $submission->grade;
        if (!$grade) abort(404);
        
        $grade->update($request->validated());
        return new GradeResource($grade);
    }

    public function show(Request $request, Submission $submission)
    {
        $grade = $submission->grade;
        if (!$grade) abort(404);
        return new GradeResource($grade);
    }
}
PHP,
];

foreach ($controllers as $name => $content) {
    file_put_contents("$dir/app/Http/Controllers/Api/V1/$name.php", $content);
}

$resources = [
    'CourseResource' => <<<'PHP'
<?php
namespace App\Http\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'lecturer_id' => $this->lecturer_id,
            'lecturer' => new UserResource($this->whenLoaded('lecturer')),
            'materials_count' => $this->whenCounted('materials'),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
PHP,
    'UserResource' => <<<'PHP'
<?php
namespace App\Http\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
        ];
    }
}
PHP,
    'MaterialResource' => <<<'PHP'
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
            'course_id' => $this->course_id,
            'title' => $this->title,
            'content' => $this->content,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
PHP,
    'AssignmentResource' => <<<'PHP'
<?php
namespace App\Http\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssignmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'course_id' => $this->course_id,
            'title' => $this->title,
            'description' => $this->description,
            'due_date' => $this->due_date?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
PHP,
    'SubmissionResource' => <<<'PHP'
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
            'file_url' => $this->file_url,
            'user' => new UserResource($this->whenLoaded('user')),
            'grade' => new GradeResource($this->whenLoaded('grade')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
PHP,
    'GradeResource' => <<<'PHP'
<?php
namespace App\Http\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GradeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'submission_id' => $this->submission_id,
            'score' => $this->score,
            'feedback' => $this->feedback,
            'graded_by' => $this->graded_by,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
PHP,
];

foreach ($resources as $name => $content) {
    file_put_contents("$dir/app/Http/Resources/$name.php", $content);
}

$requests = [
    'StoreCourseRequest' => <<<'PHP'
<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class StoreCourseRequest extends FormRequest
{
    public function authorize() { return true; }
    public function rules() {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'lecturer_id' => 'sometimes|exists:users,id',
        ];
    }
}
PHP,
    'UpdateCourseRequest' => <<<'PHP'
<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCourseRequest extends FormRequest
{
    public function authorize() { return true; }
    public function rules() {
        return [
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
        ];
    }
}
PHP,
    'StoreMaterialRequest' => <<<'PHP'
<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class StoreMaterialRequest extends FormRequest
{
    public function authorize() { return true; }
    public function rules() {
        return [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ];
    }
}
PHP,
    'UpdateMaterialRequest' => <<<'PHP'
<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMaterialRequest extends FormRequest
{
    public function authorize() { return true; }
    public function rules() {
        return [
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
        ];
    }
}
PHP,
    'StoreAssignmentRequest' => <<<'PHP'
<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class StoreAssignmentRequest extends FormRequest
{
    public function authorize() { return true; }
    public function rules() {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'due_date' => 'required|date',
        ];
    }
}
PHP,
    'UpdateAssignmentRequest' => <<<'PHP'
<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAssignmentRequest extends FormRequest
{
    public function authorize() { return true; }
    public function rules() {
        return [
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'due_date' => 'sometimes|date',
        ];
    }
}
PHP,
    'StoreSubmissionRequest' => <<<'PHP'
<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class StoreSubmissionRequest extends FormRequest
{
    public function authorize() { return true; }
    public function rules() {
        return [
            'file_url' => 'required|string',
        ];
    }
}
PHP,
    'StoreGradeRequest' => <<<'PHP'
<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class StoreGradeRequest extends FormRequest
{
    public function authorize() { return true; }
    public function rules() {
        return [
            'score' => 'required|integer|min:0|max:100',
            'feedback' => 'nullable|string',
        ];
    }
}
PHP,
    'UpdateGradeRequest' => <<<'PHP'
<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class UpdateGradeRequest extends FormRequest
{
    public function authorize() { return true; }
    public function rules() {
        return [
            'score' => 'required|integer|min:0|max:100',
            'feedback' => 'nullable|string',
        ];
    }
}
PHP,
];

foreach ($requests as $name => $content) {
    file_put_contents("$dir/app/Http/Requests/$name.php", $content);
}

echo "Files generated successfully!\n";

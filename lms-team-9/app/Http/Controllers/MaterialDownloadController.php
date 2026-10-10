<?php

namespace App\Http\Controllers;

use App\Models\Material;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class MaterialDownloadController extends Controller
{
    public function __invoke(Material $material)
    {
        Gate::authorize('view', $material);
        abort_unless($material->type === 'file' && $material->file_path, 404);
        abort_unless(Storage::disk('local')->exists($material->file_path), 404);

        return Storage::disk('local')->download($material->file_path, $material->original_name);
    }
}

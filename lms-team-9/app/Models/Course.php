<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Course extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        // Penanganan kasus cascade (Week 9 BUILD #6):
        // Hapus seluruh berkas fisik materi dan submission sebelum data database terhapus cascade
        static::deleting(function (Course $course): void {
            // Hapus berkas fisik materi
            $materialFiles = $course->materials()
                ->whereNotNull('file_path')
                ->pluck('file_path')
                ->all();

            if (! empty($materialFiles)) {
                Storage::disk('local')->delete($materialFiles);
            }
            Storage::disk('local')->deleteDirectory("materials/{$course->id}");

            // Hapus berkas fisik submission tugas pada kelas ini
            $assignmentIds = $course->assignments()->pluck('id')->all();
            if (! empty($assignmentIds)) {
                $submissionFiles = Submission::whereIn('assignment_id', $assignmentIds)
                    ->whereNotNull('file_path')
                    ->pluck('file_path')
                    ->all();

                if (! empty($submissionFiles)) {
                    Storage::disk('local')->delete($submissionFiles);
                }

                foreach ($assignmentIds as $assignmentId) {
                    Storage::disk('local')->deleteDirectory("submissions/{$assignmentId}");
                }
            }
        });
    }

    protected $fillable = [
        'code', 'name', 'description', 'sks', 'lecturer_id', 'status'
    ];

    public function lecturer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lecturer_id');
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('enrolled_at')->withTimestamps();
    }

    public function materials(): HasMany
    {
        return $this->hasMany(Material::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }
}
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Submission extends Model
{
    protected $fillable = [
        'assignment_id', 'user_id', 'file_path', 'original_name', 
        'file_size', 'note', 'submitted_at', 'is_late'
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'is_late' => 'boolean',
        ];
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function grade(): HasOne
    {
        return $this->hasOne(Grade::class);
    }
    protected static function booted(): void
    {
    static::deleting(function (Submission $submission) {

        if ($submission->file_path && Storage::disk('local')->exists($submission->file_path)) {

            Storage::disk('local')->delete($submission->file_path);
        }
    });
    }
    protected function formattedFileSize(): Attribute
{
    return Attribute::make(
        get: function () {
            $bytes = $this->file_size;

            if (!$bytes) {
                return '0 B';
            }

            $units = ['B', 'KB', 'MB', 'GB', 'TB'];
            $i = 0;

            while ($bytes >= 1024 && $i < count($units) - 1) {
                $bytes /= 1024;
                $i++;
            }

            return round($bytes, 2) . ' ' . $units[$i];
        }
    );
    }
}
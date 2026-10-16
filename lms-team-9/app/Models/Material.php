<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Material extends Model
{
    protected $fillable = [
        'course_id', 'uploaded_by', 'title', 'description', 'type', 
        'file_path', 'original_name', 'file_size', 'mime_type', 'external_url'
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function safeExternalUrl(): ?string
    {
        $scheme = parse_url($this->external_url ?? '', PHP_URL_SCHEME);

        return in_array(strtolower((string) $scheme), ['http', 'https'], true)
            ? $this->external_url
            : null;
    }
    protected static function booted(): void
    {
    static::deleting(function (Material $material) {

        if ($material->file_path && Storage::disk('local')->exists($material->file_path)) {

            Storage::disk('local')->delete($material->file_path);
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
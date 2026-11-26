<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class Attachment extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'attachable_type',
        'attachable_id',
        'uploaded_by',
        'type',
        'title',
        'file_path',
        'external_url',
        'mime_type',
        'file_size',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
        ];
    }

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function isFile(): bool
    {
        return $this->type === 'file';
    }

    public function isImage(): bool
    {
        return $this->isFile() && $this->mime_type !== null && str_starts_with($this->mime_type, 'image/');
    }

    public function getUrlAttribute(): string
    {
        if ($this->type === 'url') {
            return (string) $this->external_url;
        }

        return $this->file_path ? Storage::url($this->file_path) : '#';
    }

    public function getFormattedSizeAttribute(): string
    {
        if (! $this->file_size) {
            return '-';
        }

        if ($this->file_size >= 1048576) {
            return number_format($this->file_size / 1048576, 2) . ' MB';
        }

        return number_format($this->file_size / 1024, 1) . ' KB';
    }
}

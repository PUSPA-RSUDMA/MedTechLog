<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class DiklatTask extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'participant_name',
        'unit_id',
        'profession_id',
        'material',
        'event_date',
        'file_path',
        'file_name',
        'drive_file_id',
        'drive_url',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'event_date' => 'date',
        ];
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(DiklatUnit::class, 'unit_id');
    }

    public function profession(): BelongsTo
    {
        return $this->belongsTo(Profession::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getFileUrlAttribute(): ?string
    {
        if (! empty($this->drive_url)) {
            return $this->drive_url;
        }

        return $this->file_path ? Storage::url($this->file_path) : null;
    }

    public function getIsGoogleDriveAttribute(): bool
    {
        return ! empty($this->drive_file_id) || ! empty($this->drive_url);
    }
}

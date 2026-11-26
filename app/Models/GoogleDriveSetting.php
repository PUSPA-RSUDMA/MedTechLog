<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoogleDriveSetting extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'is_enabled',
        'client_id',
        'client_secret',
        'refresh_token',
        'folder_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
        ];
    }

    public static function current(): self
    {
        return self::query()->firstOrCreate([], [
            'is_enabled' => (bool) config('services.google_drive.enabled', false),
            'client_id' => config('services.google_drive.client_id'),
            'client_secret' => config('services.google_drive.client_secret'),
            'refresh_token' => config('services.google_drive.refresh_token'),
            'folder_id' => config('services.google_drive.folder_id'),
        ]);
    }
}

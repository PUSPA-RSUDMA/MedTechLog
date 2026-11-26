<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaSetting extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'gateway_url',
        'api_key',
        'default_target_phone',
        'is_enabled',
        'auto_remind_tasks',
        'auto_remind_logbook',
        'reminder_time',
        'session_status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'auto_remind_tasks' => 'boolean',
            'auto_remind_logbook' => 'boolean',
        ];
    }

    public static function current(): self
    {
        return self::query()->firstOrCreate([], [
            'gateway_url' => 'http://127.0.0.1:3001',
            'is_enabled' => false,
            'auto_remind_tasks' => true,
            'auto_remind_logbook' => true,
            'reminder_time' => '07:00',
            'session_status' => 'disconnected',
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaMessage extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'recipient_phone',
        'recipient_name',
        'message',
        'category',
        'status',
        'error_message',
        'sent_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    public function getWaWebUrlAttribute(): string
    {
        $digits = preg_replace('/\D+/', '', $this->recipient_phone);
        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        }

        return 'https://wa.me/' . $digits . '?text=' . rawurlencode($this->message);
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'task_h2' => 'Reminder Tugas (H-2)',
            'task_h1' => 'Reminder Tugas (H-1)',
            'logbook_h1' => 'Reminder Logbook (H-1)',
            default => 'Pesan Manual',
        };
    }
}

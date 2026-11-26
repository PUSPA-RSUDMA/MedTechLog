<?php

declare(strict_types=1);

namespace App\Enums;

enum PriorityLevel: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
    case Urgent = 'urgent';

    public function label(): string
    {
        return match ($this) {
            self::Low => 'Rendah',
            self::Medium => 'Sedang',
            self::High => 'Tinggi',
            self::Urgent => 'Mendesak',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Low => 'bg-slate-100 text-slate-700 border-slate-200',
            self::Medium => 'bg-sky-100 text-sky-800 border-sky-200',
            self::High => 'bg-amber-100 text-amber-800 border-amber-200',
            self::Urgent => 'bg-rose-100 text-rose-800 border-rose-200',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->all();
    }
}

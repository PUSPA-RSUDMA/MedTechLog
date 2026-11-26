<?php

declare(strict_types=1);

namespace App\Enums;

enum TargetStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Completed = 'completed';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draf',
            self::Active => 'Aktif',
            self::Completed => 'Selesai',
            self::Archived => 'Diarsipkan',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'bg-slate-100 text-slate-700 border-slate-200',
            self::Active => 'bg-sky-100 text-sky-800 border-sky-200',
            self::Completed => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            self::Archived => 'bg-zinc-100 text-zinc-600 border-zinc-200',
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

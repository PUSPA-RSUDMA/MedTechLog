<?php

declare(strict_types=1);

namespace App\Enums;

enum ComplexityLevel: string
{
    case Light = 'light';
    case Medium = 'medium';
    case Heavy = 'heavy';

    public function label(): string
    {
        return match ($this) {
            self::Light => 'Ringan',
            self::Medium => 'Menengah',
            self::Heavy => 'Berat',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Light => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            self::Medium => 'bg-amber-100 text-amber-800 border-amber-200',
            self::Heavy => 'bg-rose-100 text-rose-800 border-rose-200',
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

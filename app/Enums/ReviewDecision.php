<?php

declare(strict_types=1);

namespace App\Enums;

enum ReviewDecision: string
{
    case Approved = 'approved';
    case RevisionRequired = 'revision_required';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Approved => 'Disetujui',
            self::RevisionRequired => 'Minta Revisi',
            self::Rejected => 'Ditolak',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Approved => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            self::RevisionRequired => 'bg-amber-100 text-amber-800 border-amber-200',
            self::Rejected => 'bg-rose-100 text-rose-800 border-rose-200',
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

<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Supervisor = 'supervisor';
    case Staff = 'staff';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrator',
            self::Supervisor => 'Atasan / Supervisor',
            self::Staff => 'Pelaksana / Staf',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Admin => 'bg-purple-100 text-purple-800 border-purple-200',
            self::Supervisor => 'bg-sky-100 text-sky-800 border-sky-200',
            self::Staff => 'bg-teal-100 text-teal-800 border-teal-200',
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

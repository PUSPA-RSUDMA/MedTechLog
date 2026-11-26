<?php

declare(strict_types=1);

namespace App\Enums;

enum PeriodType: string
{
    case Daily = 'daily';
    case Weekly = 'weekly';
    case Monthly = 'monthly';
    case Quarterly = 'quarterly';
    case Yearly = 'yearly';

    public function label(): string
    {
        return match ($this) {
            self::Daily => 'Harian',
            self::Weekly => 'Mingguan',
            self::Monthly => 'Bulanan',
            self::Quarterly => 'Triwulanan',
            self::Yearly => 'Tahunan',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Daily => 'bg-slate-100 text-slate-700 border-slate-200',
            self::Weekly => 'bg-cyan-100 text-cyan-800 border-cyan-200',
            self::Monthly => 'bg-teal-100 text-teal-800 border-teal-200',
            self::Quarterly => 'bg-indigo-100 text-indigo-800 border-indigo-200',
            self::Yearly => 'bg-violet-100 text-violet-800 border-violet-200',
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

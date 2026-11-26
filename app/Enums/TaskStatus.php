<?php

declare(strict_types=1);

namespace App\Enums;

enum TaskStatus: string
{
    case NotStarted = 'not_started';
    case InProgress = 'in_progress';
    case OnHold = 'on_hold';
    case PendingReview = 'pending_review';
    case RevisionRequired = 'revision_required';
    case Completed = 'completed';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::NotStarted => 'Belum Dimulai',
            self::InProgress => 'Sedang Berjalan',
            self::OnHold => 'Tertunda',
            self::PendingReview => 'Menunggu Verifikasi',
            self::RevisionRequired => 'Perlu Revisi',
            self::Completed => 'Selesai',
            self::Rejected => 'Ditolak',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::NotStarted => 'bg-slate-100 text-slate-700 border-slate-200',
            self::InProgress => 'bg-amber-100 text-amber-800 border-amber-200',
            self::OnHold => 'bg-rose-100 text-rose-800 border-rose-200',
            self::PendingReview => 'bg-sky-100 text-sky-800 border-sky-200',
            self::RevisionRequired => 'bg-orange-100 text-orange-800 border-orange-200',
            self::Completed => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            self::Rejected => 'bg-red-100 text-red-800 border-red-200',
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

<?php

namespace App\Enums;

enum ApprovalStatus: string
{
    case PENDING = 'PENDING';
    case APPROVED = 'APPROVED';
    case REJECTED = 'REJECTED';
    case REVISION_REQUESTED = 'REVISION_REQUESTED';
    case SKIPPED = 'SKIPPED';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Menunggu',
            self::APPROVED => 'Disetujui',
            self::REJECTED => 'Ditolak',
            self::REVISION_REQUESTED => 'Perlu Perbaikan',
            self::SKIPPED => 'Dilewati',
        };
    }
}

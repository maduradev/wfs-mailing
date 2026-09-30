<?php

namespace App\Enums;

enum LetterStatus: string
{
    case DRAFT = 'DRAFT';
    case SUBMITTED = 'SUBMITTED';
    case IN_REVIEW = 'IN_REVIEW';
    case REVISION = 'REVISION';
    case APPROVED = 'APPROVED';
    case REJECTED = 'REJECTED';
    case ISSUED = 'ISSUED';
    case COMPLETED = 'COMPLETED';
    case ARCHIVED = 'ARCHIVED';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draf',
            self::SUBMITTED => 'Diajukan',
            self::IN_REVIEW => 'Sedang Ditinjau',
            self::REVISION => 'Perlu Perbaikan',
            self::APPROVED => 'Disetujui',
            self::REJECTED => 'Ditolak',
            self::ISSUED => 'Diterbitkan',
            self::COMPLETED => 'Selesai',
            self::ARCHIVED => 'Diarsipkan',
        };
    }
}

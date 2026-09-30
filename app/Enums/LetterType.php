<?php

namespace App\Enums;

enum LetterType: string
{
    case LEAVE = 'LEAVE';
    case MUTATION = 'MUTATION';
    case WARNING = 'WARNING';

    public function label(): string
    {
        return match ($this) {
            self::LEAVE => 'Surat Cuti',
            self::MUTATION => 'Surat Mutasi',
            self::WARNING => 'Surat Peringatan',
        };
    }
}

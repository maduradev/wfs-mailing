<?php

namespace App\Services\Shared;

use App\Enums\LetterType;
use App\Models\LetterNumberCounter;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class LetterNumberService
{
    public function next(LetterType $type, ?int $year = null): string
    {
        $year ??= (int) now()->format('Y');

        if ($year < 2000 || $year > 9999) {
            throw new InvalidArgumentException('Tahun nomor surat tidak valid.');
        }

        $typeCode = config('letters.numbering.type_codes.'.$type->value);

        if (! is_string($typeCode) || $typeCode === '') {
            throw new InvalidArgumentException('Kode jenis surat belum dikonfigurasi.');
        }

        $sequenceLength = max(1, min(12, (int) config('letters.numbering.sequence_length', 4)));
        $pattern = (string) config('letters.numbering.pattern');
        $prefix = (string) config('letters.numbering.prefix');

        $sequence = DB::transaction(function () use ($type, $year): int {
            $now = now();
            LetterNumberCounter::query()->insertOrIgnore([
                'letter_type' => $type->value,
                'year' => $year,
                'next_number' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $counter = LetterNumberCounter::query()
                ->where('letter_type', $type->value)
                ->where('year', $year)
                ->lockForUpdate()
                ->firstOrFail();

            $sequence = (int) $counter->next_number;
            $counter->increment('next_number');

            return $sequence;
        });

        return strtr($pattern, [
            '{prefix}' => $prefix,
            '{type}' => $typeCode,
            '{year}' => (string) $year,
            '{sequence}' => str_pad((string) $sequence, $sequenceLength, '0', STR_PAD_LEFT),
        ]);
    }
}

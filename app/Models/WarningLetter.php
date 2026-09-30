<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarningLetter extends Model
{
    protected $fillable = [
        'warning_level',
        'offense_on',
        'description',
        'legal_basis',
        'valid_from',
        'valid_until',
    ];

    protected function casts(): array
    {
        return [
            'offense_on' => 'date',
            'valid_from' => 'date',
            'valid_until' => 'date',
        ];
    }

    public function letter(): BelongsTo
    {
        return $this->belongsTo(Letter::class);
    }
}

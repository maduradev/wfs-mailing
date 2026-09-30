<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveLetter extends Model
{
    protected $fillable = [
        'leave_type',
        'starts_on',
        'ends_on',
        'total_days',
        'details',
        'return_address',
    ];

    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
            'total_days' => 'decimal:2',
        ];
    }

    public function letter(): BelongsTo
    {
        return $this->belongsTo(Letter::class);
    }
}

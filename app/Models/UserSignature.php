<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserSignature extends Model
{
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'uploaded_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function letterSignatures(): HasMany
    {
        return $this->hasMany(LetterSignature::class);
    }
}

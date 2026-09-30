<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LetterSignature extends Model
{
    public $timestamps = false;

    protected $guarded = ['*'];

    protected function casts(): array
    {
        return ['signed_at' => 'datetime'];
    }

    public function letter(): BelongsTo
    {
        return $this->belongsTo(Letter::class);
    }

    public function signature(): BelongsTo
    {
        return $this->belongsTo(UserSignature::class, 'user_signature_id');
    }

    public function signer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'signer_user_id');
    }
}

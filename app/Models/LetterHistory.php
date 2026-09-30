<?php

namespace App\Models;

use App\Enums\LetterStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LetterHistory extends Model
{
    public $timestamps = false;

    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'from_status' => LetterStatus::class,
            'to_status' => LetterStatus::class,
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function letter(): BelongsTo
    {
        return $this->belongsTo(Letter::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}

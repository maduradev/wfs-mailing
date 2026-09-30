<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LetterAttachment extends Model
{
    protected $guarded = ['*'];

    public function letter(): BelongsTo
    {
        return $this->belongsTo(Letter::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}

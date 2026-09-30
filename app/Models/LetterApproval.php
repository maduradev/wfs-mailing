<?php

namespace App\Models;

use App\Enums\ApprovalStatus;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LetterApproval extends Model
{
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'approver_role' => UserRole::class,
            'status' => ApprovalStatus::class,
            'resolved_at' => 'datetime',
        ];
    }

    public function letter(): BelongsTo
    {
        return $this->belongsTo(Letter::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_user_id');
    }
}

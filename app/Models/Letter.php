<?php

namespace App\Models;

use App\Enums\LetterStatus;
use App\Enums\LetterType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Letter extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'subject_user_id',
        'title',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'type' => LetterType::class,
            'status' => LetterStatus::class,
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'issued_at' => 'datetime',
            'completed_at' => 'datetime',
            'archived_at' => 'datetime',
            'issued_snapshot' => 'array',
        ];
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(User::class, 'subject_user_id');
    }

    public function leaveDetails(): HasOne
    {
        return $this->hasOne(LeaveLetter::class);
    }

    public function mutationDetails(): HasOne
    {
        return $this->hasOne(MutationLetter::class);
    }

    public function warningDetails(): HasOne
    {
        return $this->hasOne(WarningLetter::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(LetterApproval::class)->orderBy('sequence');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(LetterHistory::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(LetterAttachment::class);
    }

    public function signatures(): HasMany
    {
        return $this->hasMany(LetterSignature::class)->orderBy('signing_order');
    }

    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'subject');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MutationLetter extends Model
{
    protected $fillable = [
        'from_department_id',
        'to_department_id',
        'from_position_id',
        'to_position_id',
        'effective_on',
        'details',
    ];

    protected function casts(): array
    {
        return ['effective_on' => 'date'];
    }

    public function letter(): BelongsTo
    {
        return $this->belongsTo(Letter::class);
    }

    public function fromDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'from_department_id');
    }

    public function toDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'to_department_id');
    }

    public function fromPosition(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'from_position_id');
    }

    public function toPosition(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'to_position_id');
    }
}

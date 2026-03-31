<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'attendance_id',
    'user_id',
    'requested_clock_in_at',
    'requested_clock_out_at',
    'requested_break_times',
    'note',
    'status',
    'requested_at',
    'approved_at',
    'approved_by',
])]
class StampCorrectionRequest extends Model
{
    protected function casts(): array
    {
        return [
            'requested_clock_in_at' => 'datetime',
            'requested_clock_out_at' => 'datetime',
            'requested_break_times' => 'array',
            'requested_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}

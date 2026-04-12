<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'work_date',
    'clock_in_at',
    'clock_out_at',
    'note',
])]
class Attendance extends Model
{
    protected function casts(): array
    {
        return [
            'work_date' => 'date',
            'clock_in_at' => 'datetime',
            'clock_out_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function breaks(): HasMany
    {
        return $this->hasMany(BreakTime::class)->orderBy('started_at');
    }

    public function correctionRequests(): HasMany
    {
        return $this->hasMany(StampCorrectionRequest::class)->latest();
    }

    public function pendingCorrectionRequest(): HasMany
    {
        return $this->hasMany(StampCorrectionRequest::class)->where('status', 'pending')->latest();
    }

    public function isOnBreak(): bool
    {
        return $this->breaks->contains(fn (BreakTime $break) => $break->ended_at === null);
    }

    public function statusLabel(): string
    {
        if ($this->clock_out_at !== null) {
            return '退勤済';
        }

        if ($this->isOnBreak()) {
            return '休憩中';
        }

        if ($this->clock_in_at !== null) {
            return '出勤中';
        }

        return '勤務外';
    }

    public function totalBreakMinutes(): int
    {
        return (int) $this->breaks->sum(function (BreakTime $break) {
            if (! $break->started_at || ! $break->ended_at) {
                return 0;
            }

            return $break->started_at->diffInMinutes($break->ended_at);
        });
    }

    public function totalWorkMinutes(): int
    {
        if (! $this->clock_in_at || ! $this->clock_out_at) {
            return 0;
        }

        return max(
            $this->clock_in_at->diffInMinutes($this->clock_out_at) - $this->totalBreakMinutes(),
            0
        );
    }

    public function formattedMinutes(int $minutes): string
    {
        return sprintf('%d:%02d', intdiv($minutes, 60), $minutes % 60);
    }

    public function breakDurationLabel(): string
    {
        return $this->formattedMinutes($this->totalBreakMinutes());
    }

    public function workDurationLabel(): string
    {
        return $this->formattedMinutes($this->totalWorkMinutes());
    }
}

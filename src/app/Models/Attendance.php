<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attendance extends Model
{
    /**
     * 複数代入を許可するカラムを定義する。
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'work_date',
        'clock_in_at',
        'clock_out_at',
        'note',
    ];

    /**
     * カラムの型変換を定義する。
     *
     * @var array<string, string>
     */
    protected $casts = [
        'work_date' => 'date',
        'clock_in_at' => 'datetime',
        'clock_out_at' => 'datetime',
    ];

    /**
     * 勤怠に紐づくユーザーを取得する。
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 勤怠に紐づく休憩時間を開始時刻順で取得する。
     */
    public function breaks(): HasMany
    {
        return $this->hasMany(BreakTime::class)->orderBy('started_at');
    }

    /**
     * 勤怠に紐づく修正申請を新しい順で取得する。
     */
    public function correctionRequests(): HasMany
    {
        return $this->hasMany(StampCorrectionRequest::class)->latest();
    }

    /**
     * 勤怠に紐づく承認待ちの修正申請を取得する。
     */
    public function pendingCorrectionRequest(): HasMany
    {
        return $this->hasMany(StampCorrectionRequest::class)->where('status', 'pending')->latest();
    }

    /**
     * 現在休憩中かどうかを判定する。
     */
    public function isOnBreak(): bool
    {
        return $this->breaks->contains(fn (BreakTime $break) => $break->ended_at === null);
    }

    /**
     * 勤怠の状態を画面表示用のラベルに変換する。
     */
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

    /**
     * 休憩時間の合計分数を計算する。
     */
    public function totalBreakMinutes(): int
    {
        return (int) $this->breaks->sum(function (BreakTime $break) {
            if (! $break->started_at || ! $break->ended_at) {
                return 0;
            }

            return $break->started_at->diffInMinutes($break->ended_at);
        });
    }

    /**
     * 実働時間の合計分数を計算する。
     */
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

    /**
     * 分数を「時間:分」の形式に変換する。
     */
    public function formattedMinutes(int $minutes): string
    {
        return sprintf('%d:%02d', intdiv($minutes, 60), $minutes % 60);
    }

    /**
     * 休憩時間の合計を画面表示用に整形する。
     */
    public function breakDurationLabel(): string
    {
        return $this->formattedMinutes($this->totalBreakMinutes());
    }

    /**
     * 実働時間の合計を画面表示用に整形する。
     */
    public function workDurationLabel(): string
    {
        return $this->formattedMinutes($this->totalWorkMinutes());
    }
}

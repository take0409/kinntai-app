<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BreakTime extends Model
{
    /**
     * 複数代入を許可するカラムを定義する。
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'attendance_id',
        'started_at',
        'ended_at',
    ];

    /**
     * カラムの型変換を定義する。
     *
     * @var array<string, string>
     */
    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    /**
     * 休憩時間に紐づく勤怠を取得する。
     */
    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StampCorrectionRequest extends Model
{
    /**
     * 複数代入を許可するカラムを定義する。
     *
     * @var array<int, string>
     */
    protected $fillable = [
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
    ];

    /**
     * カラムの型変換を定義する。
     *
     * @var array<string, string>
     */
    protected $casts = [
        'requested_clock_in_at' => 'datetime',
        'requested_clock_out_at' => 'datetime',
        'requested_break_times' => 'array',
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    /**
     * 修正申請に紐づく勤怠を取得する。
     */
    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    /**
     * 修正申請を作成したユーザーを取得する。
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 修正申請を承認した管理者を取得する。
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}

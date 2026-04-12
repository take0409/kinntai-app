<?php

namespace App\Http\Requests;

class AdminAttendanceUpdateRequest extends AttendanceCorrectionRequest
{
    /**
     * 管理者だけが勤怠を直接更新できるようにする。
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }
}

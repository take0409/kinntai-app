<?php

namespace App\Http\Requests;

class AdminAttendanceUpdateRequest extends AttendanceCorrectionRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }
}

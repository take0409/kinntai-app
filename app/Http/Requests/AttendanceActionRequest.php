<?php

namespace App\Http\Requests;

use App\Models\Attendance;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;

class AttendanceActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && ! $this->user()->is_admin;
    }

    public function rules(): array
    {
        return [];
    }

    public function currentTime(): CarbonImmutable
    {
        return CarbonImmutable::now(config('app.timezone'));
    }

    public function todayAttendance(): ?Attendance
    {
        return Attendance::query()
            ->with('breaks')
            ->where('user_id', $this->user()->id)
            ->whereDate('work_date', $this->currentTime()->toDateString())
            ->first();
    }
}

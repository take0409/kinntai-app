<?php

namespace App\Http\Requests;

use App\Models\Attendance;
use Illuminate\Foundation\Http\FormRequest;

class UserAttendanceDetailRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Attendance|null $attendance */
        $attendance = $this->route('attendance');

        return $attendance !== null
            && $this->user() !== null
            && ! $this->user()->is_admin
            && $attendance->user_id === $this->user()->id;
    }

    protected function failedAuthorization(): void
    {
        abort(404);
    }

    public function rules(): array
    {
        return [];
    }
}

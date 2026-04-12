<?php

namespace App\Http\Requests;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;

class AdminAttendanceListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => ['nullable', 'date_format:Y-m-d'],
        ];
    }

    public function attributes(): array
    {
        return [
            'date' => '対象日',
        ];
    }

    public function targetDate(): CarbonImmutable
    {
        $date = $this->string('date')->toString();

        return CarbonImmutable::parse($date !== '' ? $date : now()->toDateString());
    }
}

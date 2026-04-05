<?php

namespace App\Http\Requests;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;

class UserAttendanceListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'month' => ['nullable', 'date_format:Y-m'],
        ];
    }

    public function attributes(): array
    {
        return [
            'month' => '対象月',
        ];
    }

    public function selectedMonth(): CarbonImmutable
    {
        $month = $this->string('month')->toString();

        return CarbonImmutable::parse($month !== '' ? $month : now()->format('Y-m'));
    }
}

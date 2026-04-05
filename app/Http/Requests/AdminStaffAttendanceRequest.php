<?php

namespace App\Http\Requests;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;

class AdminStaffAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var User|null $user */
        $user = $this->route('user');

        return $user !== null && ! $user->is_admin;
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

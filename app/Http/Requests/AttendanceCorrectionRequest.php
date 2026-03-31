<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Validator;

class AttendanceCorrectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'clock_in' => ['required', 'date_format:H:i'],
            'clock_out' => ['required', 'date_format:H:i'],
            'break1_start' => ['nullable', 'date_format:H:i'],
            'break1_end' => ['nullable', 'date_format:H:i'],
            'break2_start' => ['nullable', 'date_format:H:i'],
            'break2_end' => ['nullable', 'date_format:H:i'],
            'note' => ['required', 'string', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'clock_in' => '出勤時間',
            'clock_out' => '退勤時間',
            'break1_start' => '休憩時間',
            'break1_end' => '休憩時間',
            'break2_start' => '休憩時間',
            'break2_end' => '休憩時間',
            'note' => '備考',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $clockIn = $this->parseTime('clock_in');
            $clockOut = $this->parseTime('clock_out');

            if (! $clockIn || ! $clockOut || $clockOut->lessThanOrEqualTo($clockIn)) {
                $validator->errors()->add('clock_in', '出勤時間もしくは退勤時間が不適切な値です');
            }

            foreach ([1, 2] as $index) {
                $start = $this->parseTime("break{$index}_start");
                $end = $this->parseTime("break{$index}_end");

                if (($start && ! $end) || (! $start && $end)) {
                    $validator->errors()->add("break{$index}_start", '休憩時間が不適切な値です');
                    continue;
                }

                if (! $start || ! $end || ! $clockIn || ! $clockOut) {
                    continue;
                }

                if ($end->lessThanOrEqualTo($start) || $start->lessThan($clockIn) || $end->greaterThan($clockOut)) {
                    $validator->errors()->add("break{$index}_start", '休憩時間が不適切な値です');
                }
            }
        });
    }

    public function breakTimes(): array
    {
        $breaks = [];

        foreach ([1, 2] as $index) {
            $start = $this->input("break{$index}_start");
            $end = $this->input("break{$index}_end");

            if ($start && $end) {
                $breaks[] = [
                    'start' => $start,
                    'end' => $end,
                ];
            }
        }

        return $breaks;
    }

    protected function parseTime(string $key): ?Carbon
    {
        $value = $this->input($key);

        if (! $value) {
            return null;
        }

        try {
            return Carbon::createFromFormat('H:i', $value, config('app.timezone'));
        } catch (\Throwable) {
            return null;
        }
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StampCorrectionRequestIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', 'in:pending,approved'],
        ];
    }

    public function attributes(): array
    {
        return [
            'status' => '申請ステータス',
        ];
    }

    public function statusFilter(): string
    {
        return $this->string('status')->toString() === 'approved'
            ? 'approved'
            : 'pending';
    }
}

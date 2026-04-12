<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StampCorrectionApprovalRequest extends FormRequest
{
    /**
     * 管理者だけが勤怠修正申請を承認できるようにする。
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    /**
     * 承認処理は入力項目を持たない。
     */
    public function rules(): array
    {
        return [];
    }
}

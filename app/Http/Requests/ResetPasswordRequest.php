<?php

namespace App\Http\Requests;

use App\Actions\Fortify\PasswordValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    use PasswordValidationRules;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'password' => $this->passwordRules(),
        ];
    }

    public function attributes(): array
    {
        return [
            'password' => 'パスワード',
        ];
    }
}

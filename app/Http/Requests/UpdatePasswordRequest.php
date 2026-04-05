<?php

namespace App\Http\Requests;

use App\Actions\Fortify\PasswordValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePasswordRequest extends FormRequest
{
    use PasswordValidationRules;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string', 'current_password:web'],
            'password' => $this->passwordRules(),
        ];
    }

    public function attributes(): array
    {
        return [
            'current_password' => '現在のパスワード',
            'password' => 'パスワード',
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.current_password' => '現在のパスワードが正しくありません。',
        ];
    }
}

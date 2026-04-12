<?php

namespace App\Http\Requests;

use App\Actions\Fortify\PasswordValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    use PasswordValidationRules;

    /**
     * パスワード再設定フォームの送信を許可する。
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 再設定するパスワードの入力ルールを定義する。
     */
    public function rules(): array
    {
        return [
            'password' => $this->passwordRules(),
        ];
    }

    /**
     * バリデーションメッセージで表示する項目名を定義する。
     */
    public function attributes(): array
    {
        return [
            'password' => 'パスワード',
        ];
    }
}

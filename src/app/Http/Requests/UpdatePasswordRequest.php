<?php

namespace App\Http\Requests;

use App\Actions\Fortify\PasswordValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePasswordRequest extends FormRequest
{
    use PasswordValidationRules;

    /**
     * パスワード更新フォームの送信を許可する。
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 現在のパスワードと新しいパスワードの入力ルールを定義する。
     */
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string', 'current_password:web'],
            'password' => $this->passwordRules(),
        ];
    }

    /**
     * バリデーションメッセージで表示する項目名を定義する。
     */
    public function attributes(): array
    {
        return [
            'current_password' => '現在のパスワード',
            'password' => 'パスワード',
        ];
    }

    /**
     * 現在のパスワード不一致時のメッセージを定義する。
     */
    public function messages(): array
    {
        return [
            'current_password.current_password' => '現在のパスワードが正しくありません。',
        ];
    }
}

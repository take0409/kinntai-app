<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminLoginRequest extends FormRequest
{
    /**
     * 管理者ログインフォームの送信を許可する。
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 管理者ログインに必要な入力項目を定義する。
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * バリデーションメッセージで表示する項目名を定義する。
     */
    public function attributes(): array
    {
        return [
            'email' => 'メールアドレス',
            'password' => 'パスワード',
        ];
    }
}

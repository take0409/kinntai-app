<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileInformationRequest extends FormRequest
{
    /**
     * プロフィール更新フォームの送信を許可する。
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 名前とメールアドレスの入力ルールを定義する。
     */
    public function rules(): array
    {
        $user = $this->user();

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user?->id),
            ],
        ];
    }

    /**
     * バリデーションメッセージで表示する項目名を定義する。
     */
    public function attributes(): array
    {
        return [
            'name' => 'お名前',
            'email' => 'メールアドレス',
        ];
    }
}

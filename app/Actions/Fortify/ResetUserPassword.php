<?php

namespace App\Actions\Fortify;

use App\Http\Requests\ResetPasswordRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\ResetsUserPasswords;

class ResetUserPassword implements ResetsUserPasswords
{
    /**
     * Validate and reset the user's forgotten password.
     *
     * @param  array<string, string>  $input
     *
     * @throws ValidationException
     */
    public function reset(User $user, array $input): void
    {
        $resetPasswordRequest = new ResetPasswordRequest;

        Validator::make(
            $input,
            $resetPasswordRequest->rules(),
            [],
            $resetPasswordRequest->attributes()
        )->validate();

        $user->forceFill([
            'password' => Hash::make($input['password']),
        ])->save();
    }
}

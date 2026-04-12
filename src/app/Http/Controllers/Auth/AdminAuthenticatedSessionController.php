<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminLoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AdminAuthenticatedSessionController extends Controller
{
    /**
     * 管理者ログイン画面を表示する。
     */
    public function create()
    {
        if (auth()->check() && auth()->user()->is_admin) {
            return redirect('/admin/attendance/list');
        }

        return view('auth.admin-login');
    }

    /**
     * 管理者ログインを実行する。
     */
    public function store(AdminLoginRequest $request): RedirectResponse
    {
        $user = User::query()
            ->where('email', strtolower($request->string('email')->toString()))
            ->where('is_admin', true)
            ->first();

        if (! $user || ! Hash::check($request->string('password')->toString(), $user->password)) {
            throw ValidationException::withMessages([
                'email' => [trans('auth.failed')],
            ]);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended('/admin/attendance/list');
    }
}

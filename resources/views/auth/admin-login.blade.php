@extends('layouts.guest')

@section('content')
    <section class="auth-card">
        <h1 class="auth-card-title">管理者ログイン</h1>
        <form action="/admin/login" method="post" class="auth-form">
            @csrf
            <div class="field-group">
                <label for="email">メールアドレス</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" class="{{ $errors->has('email') ? 'input-error' : '' }}">
                @error('email')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <div class="field-group">
                <label for="password">パスワード</label>
                <input id="password" type="password" name="password" class="{{ $errors->has('password') ? 'input-error' : '' }}">
                @error('password')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <button type="submit" class="button button-primary">管理者ログインする</button>
        </form>
    </section>
@endsection

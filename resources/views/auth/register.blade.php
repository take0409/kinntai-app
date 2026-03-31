@extends('layouts.guest')

@section('content')
    <section class="auth-card">
        <h1 class="auth-card__title">会員登録</h1>
        <form action="/register" method="post" class="auth-form">
            @csrf
            <div class="field-group">
                <label>名前</label>
                <input type="text" name="name" value="{{ old('name') }}" class="{{ $errors->has('name') ? 'input-error' : '' }}">
                @error('name')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <div class="field-group">
                <label>メールアドレス</label>
                <input type="email" name="email" value="{{ old('email') }}" class="{{ $errors->has('email') ? 'input-error' : '' }}">
                @error('email')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <div class="field-group">
                <label>パスワード</label>
                <input type="password" name="password" class="{{ $errors->has('password') ? 'input-error' : '' }}">
                @error('password')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <div class="field-group">
                <label>パスワード確認</label>
                <input type="password" name="password_confirmation" class="{{ $errors->has('password_confirmation') ? 'input-error' : '' }}">
            </div>
            <button type="submit" class="button button--primary">登録する</button>
        </form>
        <a href="/login" class="link-text">ログインはこちら</a>
    </section>
@endsection

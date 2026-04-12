@extends('layouts.guest')

@section('content')
    <section class="verify-card">
        <h1 class="sr-only">メール認証</h1>
        <p>登録していただいたメールアドレスに認証メールを送付しました。<br>メール認証を完了してください。</p>
        <a href="http://localhost:8025" class="button button-secondary" target="_blank" rel="noopener noreferrer">認証はこちらから</a>
        <form action="/email/verification-notification" method="post">
            @csrf
            <button type="submit" class="link-button">認証メールを再送する</button>
        </form>
    </section>
@endsection

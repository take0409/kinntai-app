@extends('layouts.app')

@section('content')
    <section class="table-section">
        <h1 class="section-title">スタッフ一覧</h1>
        <table class="data-table">
            <caption class="sr-only">スタッフ一覧</caption>
            <thead>
                <tr>
                    <th scope="col">名前</th>
                    <th scope="col">メールアドレス</th>
                    <th scope="col">月次勤怠</th>
                </tr>
            </thead>
            <tbody>
                @foreach($staffUsers as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td><a href="/admin/attendance/staff/{{ $user->id }}">詳細</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>
@endsection

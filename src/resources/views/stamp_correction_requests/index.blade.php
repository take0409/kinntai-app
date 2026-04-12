@extends('layouts.app')

@section('content')
    <section class="table-section">
        <h1 class="section-title">申請一覧</h1>
        <div class="tab-nav">
            <a href="?status=pending" class="{{ $status === 'pending' ? 'is-active' : '' }}" aria-current="{{ $status === 'pending' ? 'page' : 'false' }}">承認待ち</a>
            <a href="?status=approved" class="{{ $status === 'approved' ? 'is-active' : '' }}" aria-current="{{ $status === 'approved' ? 'page' : 'false' }}">承認済み</a>
        </div>
        <table class="data-table">
            <caption class="sr-only">申請一覧</caption>
            <thead>
                <tr>
                    <th scope="col">状態</th>
                    <th scope="col">名前</th>
                    <th scope="col">対象日時</th>
                    <th scope="col">申請理由</th>
                    <th scope="col">申請日時</th>
                    <th scope="col">詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach($requests as $requestItem)
                    <tr>
                        <td>{{ $requestItem->status === 'pending' ? '承認待ち' : '承認済み' }}</td>
                        <td>{{ $requestItem->user->name }}</td>
                        <td>{{ $requestItem->attendance->work_date->format('Y/m/d') }}</td>
                        <td>{{ $requestItem->note }}</td>
                        <td>{{ $requestItem->requested_at->format('Y/m/d') }}</td>
                        <td>
                            @if($isAdmin)
                                <a href="/stamp_correction_request/approve/{{ $requestItem->id }}">詳細</a>
                            @else
                                <a href="/attendance/detail/{{ $requestItem->attendance_id }}">詳細</a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>
@endsection

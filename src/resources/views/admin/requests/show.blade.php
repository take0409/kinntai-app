@extends('layouts.app')

@section('content')
    <section class="detail-section">
        <h1 class="section-title">勤怠詳細</h1>
        <div class="detail-card">
            <div class="detail-row"><span>名前</span><strong>{{ $requestItem->user->name }}</strong></div>
            <div class="detail-row"><span>日付</span><strong>{{ $requestItem->attendance->work_date->format('Y年') }} {{ $requestItem->attendance->work_date->format('n月j日') }}</strong></div>
            <div class="detail-row"><span>出勤・退勤</span><div class="time-pair"><strong>{{ $requestItem->requested_clock_in_at->format('H:i') }}</strong><span>〜</span><strong>{{ $requestItem->requested_clock_out_at->format('H:i') }}</strong></div></div>
            @foreach ($breaks as $index => $break)
                <div class="detail-row"><span>休憩{{ $index === 0 ? '' : $index + 1 }}</span><div class="time-pair"><strong>{{ $break['start'] ?? '' }}</strong><span>〜</span><strong>{{ $break['end'] ?? '' }}</strong></div></div>
            @endforeach
            <div class="detail-row"><span>備考</span><strong>{{ $requestItem->note }}</strong></div>
            <div class="detail-actions">
                @if($requestItem->status === 'pending')
                    <form action="/stamp_correction_request/approve/{{ $requestItem->id }}" method="post">@csrf<button class="button button-primary">承認</button></form>
                @else
                    <button class="button button-muted" disabled>承認済み</button>
                @endif
            </div>
        </div>
    </section>
@endsection

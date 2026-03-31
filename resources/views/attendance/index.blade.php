@extends('layouts.app')

@section('content')
    <section class="stamp-card">
        <span class="status-pill">{{ $status }}</span>
        <h1 class="stamp-card__date">{{ $today->isoFormat('YYYY年M月D日(ddd)') }}</h1>
        <p class="stamp-card__time">{{ $today->format('H:i') }}</p>

        <div class="stamp-card__actions">
            @if ($status === '勤務外')
                <form action="{{ route('attendance.clock-in') }}" method="post">@csrf <button class="button button--primary">出勤</button></form>
            @elseif ($status === '出勤中')
                <form action="{{ route('attendance.clock-out') }}" method="post">@csrf <button class="button button--primary">退勤</button></form>
                <form action="{{ route('attendance.break-start') }}" method="post">@csrf <button class="button button--light">休憩入</button></form>
            @elseif ($status === '休憩中')
                <form action="{{ route('attendance.break-end') }}" method="post">@csrf <button class="button button--light">休憩戻</button></form>
            @else
                <p class="stamp-card__message">お疲れ様でした。</p>
            @endif
        </div>
    </section>
@endsection

@extends('layouts.app')

@section('content')
    <section class="detail-section">
        <h1 class="section-title">勤怠詳細</h1>
        <form action="/attendance/detail/{{ $attendance->id }}" method="post" class="detail-card">
            @csrf
            <div class="detail-row"><span>名前</span><strong>{{ $attendance->user->name }}</strong></div>
            <div class="detail-row"><span>日付</span><strong>{{ $attendance->work_date->format('Y年') }} {{ $attendance->work_date->format('n月j日') }}</strong></div>
            <div class="detail-row">
                <span>出勤・退勤</span>
                <div>
                    <div class="time-pair"><input name="clock_in" value="{{ old('clock_in', $attendance->clock_in_at?->format('H:i')) }}" class="{{ $errors->has('clock_in') ? 'input-error' : '' }}" {{ $pendingRequest ? 'disabled' : '' }}><span>〜</span><input name="clock_out" value="{{ old('clock_out', $attendance->clock_out_at?->format('H:i')) }}" class="{{ $errors->has('clock_in') ? 'input-error' : '' }}" {{ $pendingRequest ? 'disabled' : '' }}></div>
                    @error('clock_in')<p class="field-error">{{ $message }}</p>@enderror
                </div>
            </div>
            @foreach ($breaks as $index => $break)
                <div class="detail-row">
                    <span>休憩{{ $index === 0 ? '' : $index + 1 }}</span>
                    <div>
                        <div class="time-pair"><input name="break{{ $index + 1 }}_start" value="{{ old('break'.($index + 1).'_start', $break?->started_at?->format('H:i') ?? data_get($break, 'start')) }}" class="{{ $errors->has('break'.($index + 1).'_start') ? 'input-error' : '' }}" {{ $pendingRequest ? 'disabled' : '' }}><span>〜</span><input name="break{{ $index + 1 }}_end" value="{{ old('break'.($index + 1).'_end', $break?->ended_at?->format('H:i') ?? data_get($break, 'end')) }}" class="{{ $errors->has('break'.($index + 1).'_start') ? 'input-error' : '' }}" {{ $pendingRequest ? 'disabled' : '' }}></div>
                        @error('break'.($index + 1).'_start')<p class="field-error">{{ $message }}</p>@enderror
                    </div>
                </div>
            @endforeach
            <div class="detail-row">
                <span>備考</span>
                <div>
                    <textarea name="note" class="{{ $errors->has('note') ? 'input-error' : '' }}" {{ $pendingRequest ? 'disabled' : '' }}>{{ old('note', $pendingRequest?->note ?? $attendance->note) }}</textarea>
                    @error('note')<p class="field-error">{{ $message }}</p>@enderror
                    @error('attendance')<p class="field-error">{{ $message }}</p>@enderror
                </div>
            </div>
            @if($pendingRequest)
                <p class="notice-text">承認待ちのため修正はできません。</p>
            @else
                <div class="detail-actions"><button class="button button-primary">修正</button></div>
            @endif
        </form>
    </section>
@endsection

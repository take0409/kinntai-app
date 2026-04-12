@extends('layouts.app')

@section('content')
    <section class="table-section table-section-wide">
        <h1 class="section-title">{{ $user->name }}さんの勤怠</h1>
        <div class="calendar-nav">
            <a href="?month={{ $month->subMonth()->format('Y-m') }}">← 前月</a>
            <span>{{ $month->format('Y/m') }}</span>
            <a href="?month={{ $month->addMonth()->format('Y-m') }}">翌月 →</a>
        </div>
        <table class="data-table">
            <caption class="sr-only">{{ $user->name }}さんの月次勤怠一覧</caption>
            <thead>
                <tr>
                    <th scope="col">日付</th>
                    <th scope="col">出勤</th>
                    <th scope="col">退勤</th>
                    <th scope="col">休憩</th>
                    <th scope="col">合計</th>
                    <th scope="col">詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $row)
                    <tr>
                        <td>{{ $row['date']->format('m/d(D)') }}</td>
                        <td>{{ $row['attendance']?->clock_in_at?->format('H:i') }}</td>
                        <td>{{ $row['attendance']?->clock_out_at?->format('H:i') }}</td>
                        <td>{{ $row['attendance']?->breakDurationLabel() }}</td>
                        <td>{{ $row['attendance']?->workDurationLabel() }}</td>
                        <td>@if($row['attendance'])<a href="/admin/attendance/{{ $row['attendance']->id }}">詳細</a>@endif</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="detail-actions">
            <a href="{{ route('admin.staff.csv', ['user' => $user->id, 'month' => $month->format('Y-m')]) }}" class="button button-primary">CSV出力</a>
        </div>
    </section>
@endsection

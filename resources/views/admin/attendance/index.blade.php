@extends('layouts.app')

@section('content')
    <section class="table-section">
        <h1 class="section-title">{{ $date->format('Y年n月j日') }}の勤怠</h1>
        <div class="calendar-nav">
            <a href="?date={{ $date->subDay()->format('Y-m-d') }}">← 前日</a>
            <span>{{ $date->format('Y/m/d') }}</span>
            <a href="?date={{ $date->addDay()->format('Y-m-d') }}">翌日 →</a>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>名前</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $row)
                    <tr>
                        <td>{{ $row['user']->name }}</td>
                        <td>{{ $row['attendance']?->clock_in_at?->format('H:i') }}</td>
                        <td>{{ $row['attendance']?->clock_out_at?->format('H:i') }}</td>
                        <td>{{ $row['attendance']?->breakDurationLabel() }}</td>
                        <td>{{ $row['attendance']?->workDurationLabel() }}</td>
                        <td>@if($row['attendance'])<a href="/admin/attendance/{{ $row['attendance']->id }}">詳細</a>@endif</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>
@endsection

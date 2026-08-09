@extends('layouts.app')

@section('title', 'Athlete Profile | UM Attendance')

@section('content')
<div class="page-panel">
    <div class="large-box">
        <div style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 18px; align-items: flex-start;">
            <div>
                <h2 class="page-title">{{ $student['first_name'] }} {{ $student['last_name'] }}</h2>
                <p class="hero-copy">Profile and attendance calendar for {{ ucfirst($student['sport']) }}. Change the month to view performance details.</p>
            </div>
            <div style="display: grid; gap: 10px; text-align: right;">
                <span class="badge">ID: {{ $student['student_id'] }}</span>
                <span class="badge">Sport: {{ ucfirst($student['sport']) }}</span>
            </div>
        </div>

        <div style="display: flex; gap: 16px; flex-wrap: wrap; margin-top: 24px; align-items: center;">
            <div style="display: flex; gap: 16px; align-items: center;">
                @if(!empty($student['avatar']))
                    <img src="{{ $student['avatar'] }}" alt="{{ $student['first_name'] }} {{ $student['last_name'] }}" class="avatar avatar-image" />
                @else
                    <div class="avatar">{{ strtoupper(substr($student['first_name'],0,1)) }}{{ strtoupper(substr($student['last_name'],0,1)) }}</div>
                @endif
                <div>
                    <h3 style="margin: 0 0 3px;">{{ $student['first_name'] }} {{ $student['middle_name'] ? $student['middle_name'] . ' ' : '' }}{{ $student['last_name'] }}</h3>
                    <p style="margin: 0; color: var(--um-gray);">{{ $student['course'] }} · Year {{ $student['year_level'] }}</p>
                </div>
            </div>
            <div style="display: grid; gap: 10px; align-items: start;">
                <span class="badge">Present: {{ $counts['present'] }}</span>
                <span class="badge">Late: {{ $counts['late'] }}</span>
                <span class="badge">Absent: {{ $counts['absent'] }}</span>
                <span class="badge">Excuse: {{ $counts['excuse'] }}</span>
            </div>
        </div>

        <form method="GET" class="mobile-form-stack" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center; margin-top: 28px;">
            <label style="color: var(--um-gray);">Month</label>
            <input type="number" name="month" value="{{ $month }}" min="1" max="12" class="form-control" style="max-width: 120px;" />
            <input type="number" name="year" value="{{ $year }}" min="2023" class="form-control" style="max-width: 140px;" />
            <button class="button button-secondary" type="submit">Update</button>
        </form>

        <div class="calendar-grid">
            @foreach($calendar as $date => $entry)
                @php
                    $day = date('j', strtotime($date));
                    $status = $entry['status'] ?? 'none';
                    $time = $entry['time'] ?? '--';
                    if (is_string($time) && preg_match('/^\d{1,2}:\d{2}$/', $time)) {
                        $time = date('g:i a', strtotime($time));
                    }
                @endphp
                <div class="calendar-cell">
                    <div>
                        <h4>{{ $day }}</h4>
                        <p>{{ date('D', strtotime($date)) }}</p>
                    </div>
                    @if($status !== 'none')
                        <span class="status-chip {{ $status }}">{{ ucfirst($status) }} · {{ $time }}</span>
                    @else
                        <span class="status-chip info">No attendance recorded</span>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

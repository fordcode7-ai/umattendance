@extends('layouts.app')

@section('title', 'Schedule | UM Attendance')

@section('content')
<div class="page-panel">
    <div class="large-box">
        <h2 class="page-title">My {{ ucfirst($user['sport']) }} Schedule</h2>
        <p class="hero-copy">Training schedule is shown in calendar style with venue and time inside each date block.</p>

        @if(!empty($unreadSchedule))
            <x-notification-card
                title="New Schedule Update"
                description="Your coach has posted a new schedule update for your team. Acknowledge it to clear the bell notification."
                actionUrl="/student/notifications/acknowledge/schedule"
                actionLabel="Acknowledge"
            />
        @endif

        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom: 20px;">
            <div style="display:flex; align-items:center; gap:12px;">
                <span style="font-weight:600; color: var(--um-gray);">Month:</span>
                <form method="GET" action="/student/schedule" style="margin:0;">
                    <select name="month" id="month" onchange="this.form.submit()" class="form-control form-select" style="min-width: 240px;">
                        @foreach($availableMonths as $value => $label)
                            <option value="{{ $value }}" {{ $value === $monthKey ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <a href="/student/schedule?month={{ $prevMonth }}" class="button button-secondary">&laquo; Prev</a>
                <a href="/student/schedule?month={{ $nextMonth }}" class="button button-secondary">Next &raquo;</a>
            </div>
        </div>

        @if(!empty($schedulePublishedAt))
            <div class="page-notice page-notice-success" style="margin-bottom: 20px;">
                <span class="page-notice-dot"></span>
                <div>
                    <strong>Published:</strong>
                    <p>{{ date('F j, Y g:i A', strtotime($schedulePublishedAt)) }}</p>
                </div>
            </div>
        @endif

        @php
            $year = date('Y');
            $month = date('m');
            $firstOfMonth = strtotime("{$year}-{$month}-01");
            $daysInMonth = date('t', $firstOfMonth);
        @endphp

        <div class="month-grid">
            @for($day = 1; $day <= $daysInMonth; $day++)
                @php
                    $date = sprintf('%s-%02d', date('Y-m', $firstOfMonth), $day);
                    $item = $schedules[$date] ?? null;
                    $dayName = date('D', strtotime($date));
                    $isSunday = $dayName === 'Sun';
                    $bodyLabel = '';
                    if ($item) {
                        if (isset($item['time']) && $item['time'] === 'No Training') {
                            $bodyLabel = 'No Training';
                        } else {
                            $bodyLabel = 'Venue: ' . ($item['venue'] ?? 'TBA') . ' · Time: ' . App\Services\AttendanceStore::formatDisplayTime($item['time'] ?? null);
                        }
                    } elseif ($isSunday) {
                        $bodyLabel = 'No Training';
                    } else {
                        $bodyLabel = 'No schedule';
                    }
                @endphp
                <div class="calendar-cell month-cell {{ $item ? 'has-schedule' : ($isSunday ? 'rest-day' : 'empty-day') }}">
                    <div>
                        <h4>{{ $day }} <span style="color: var(--um-gray); font-size: 0.95rem;">{{ $dayName }}</span></h4>
                        <p>{{ date('M j, Y', strtotime($date)) }}</p>
                    </div>
                    <div style="margin-top: 12px;">
                        @if($item && isset($item['time']) && $item['time'] === 'No Training')
                            <span class="status-chip info">No Training</span>
                        @elseif($item)
                            <span class="status-chip info">Venue: {{ $item['venue'] ?? 'TBA' }}</span>
                            <span class="status-chip info">Time: {{ App\Services\AttendanceStore::formatDisplayTime($item['time'] ?? null) }}</span>
                        @elseif($isSunday)
                            <span class="status-chip info">No Training</span>
                        @else
                            <span class="status-chip info">No schedule</span>
                        @endif
                    </div>
                </div>
            @endfor
        </div>
    </div>
</div>
@endsection

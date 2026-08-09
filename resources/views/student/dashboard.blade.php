@extends('layouts.app')

@section('title', 'Student Dashboard | UM Attendance')

@section('content')
<div class="page-panel">
    <div class="grid-two">
        <div class="large-box">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; flex-wrap: wrap;">
                <div>
                    <h2 class="page-title">Welcome, {{ $user['first_name'] }} </h2>
                    <p class="hero-copy">Your sport: {{ ucfirst($user['sport']) }} · Student ID: {{ $user['student_id'] }}</p>
                </div>
                <div style="display: grid; gap: 10px; text-align: right;">
                    <span class="badge">Year {{ $user['year_level'] }}</span>
                    <span class="badge">{{ $user['course'] }}</span>
                </div>
            </div>

            <div class="status-key">
                <span><span class="dot green"></span> Present</span>
                <span><span class="dot yellow"></span> Late</span>
                <span><span class="dot red"></span> Absent</span>
                <span><span class="dot blue"></span> Excuse</span>
                <span><span class="dot teal"></span> Special Training</span>
                <span><span class="dot gray"></span> No Training</span>
            </div>

            <div class="table-grid action-grid" style="margin-top: 24px;">
                <form method="POST" action="/student/attend">@csrf<button type="submit" name="action" value="present" class="button button-present" {{ $isTodaySunday || (!empty($todayAttendance) && $todayAttendance['status'] === 'no_training') ? 'disabled' : '' }}>Present</button></form>
                <form method="POST" action="/student/attend">@csrf<button type="submit" name="action" value="late" class="button button-late" {{ $isTodaySunday || (!empty($todayAttendance) && $todayAttendance['status'] === 'no_training') ? 'disabled' : '' }}>Late</button></form>
                <form method="POST" action="/student/attend">@csrf<button type="submit" name="action" value="absent" class="button button-absent" {{ $isTodaySunday || (!empty($todayAttendance) && $todayAttendance['status'] === 'no_training') ? 'disabled' : '' }}>Absent</button></form>
                <form method="GET" action="/student/excuse"><button type="submit" class="button button-excuse">Request Excuse</button></form>
            </div>

            <div style="margin-top: 28px; display: flex; flex-wrap: wrap; gap: 12px; align-items: center; justify-content: space-between;">
                <div style="display: flex; flex-wrap: wrap; gap: 12px; align-items: center;">
                    <span class="badge">Month: {{ $monthName }} {{ $year }}</span>
                    <span class="badge">Present: {{ $counts['present'] }}</span>
                    <span class="badge">Late: {{ $counts['late'] }}</span>
                    <span class="badge">Absent: {{ $counts['absent'] }}</span>
                    <span class="badge">Excuse: {{ $counts['excuse'] }}</span>
                    <span class="badge">Special Training: {{ $counts['special_training'] }}</span>
                        
                </div>
                <div style="display: flex; gap: 8px; align-items: center;">
                    @php
                        $prevMonth = $month - 1;
                        $prevYear = $year;
                        if ($prevMonth < 1) {
                            $prevMonth = 12;
                            $prevYear--;
                        }
                        $nextMonth = $month + 1;
                        $nextYear = $year;
                        if ($nextMonth > 12) {
                            $nextMonth = 1;
                            $nextYear++;
                        }
                        $isCurrentMonth = $month === now()->month && $year === now()->year;
                    @endphp
                    <a href="/student/dashboard?year={{ $prevYear }}&month={{ $prevMonth }}" class="button button-secondary" style="padding: 10px 14px; font-size: 0.9rem;"><i class="bi bi-chevron-left"></i> Previous</a>
                    @if (!$isCurrentMonth)
                        <a href="/student/dashboard" class="button button-primary" style="padding: 10px 14px; font-size: 0.9rem;">Today</a>
                    @endif
                    <a href="/student/dashboard?year={{ $nextYear }}&month={{ $nextMonth }}" class="button button-secondary" style="padding: 10px 14px; font-size: 0.9rem;">Next <i class="bi bi-chevron-right"></i></a>
                </div>
            </div>

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
                            <h4>{{ $day }} <span style="color: var(--um-gray); font-size: 0.95rem;">{{ date('D', strtotime($date)) }}</span></h4>
                            <p>{{ $date }}</p>
                        </div>
                        @if($status !== 'none')
                            <span class="status-chip {{ $status }}">{{ ucfirst($status) }} · {{ $time }}</span>
                        @else
                            <span class="status-chip info">No attendance</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <div class="large-box">
            <h3 style="margin-top: 0;">Profile Snapshot</h3>
            <div style="display: grid; gap: 22px;">
                <div style="display: flex; gap: 18px; align-items: center;">
                    @if(!empty($user['avatar']))
                        <img src="{{ $user['avatar'] }}" alt="Profile photo" class="avatar avatar-image" />
                    @else
                        <div class="avatar">{{ strtoupper(substr($user['first_name'],0,1)) }}{{ strtoupper(substr($user['last_name'],0,1)) }}</div>
                    @endif
                    <div>
                        <h3 style="margin: 0;">{{ $user['first_name'] }} {{ $user['middle_name'] ? $user['middle_name'] . ' ' : '' }}{{ $user['last_name'] }}</h3>
                        <p style="margin: 6px 0 0; color: var(--um-gray);">{{ $user['contact'] }}</p>
                        <p style="margin: 4px 0 0; color: var(--um-gray);">{{ ucfirst($user['sport']) }} athlete</p>
                    </div>
                </div>

                <div class="form-card">
                    <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; margin-bottom:18px;">
                        <h4 style="margin:0;">Upcoming Training</h4>
                        @if(!empty($unreadSchedule))
                            <span style="display:inline-flex; align-items:center; padding:6px 10px; border-radius:999px; background:rgba(212,175,55,0.14); color:#f4c542; font-size:0.82rem; font-weight:700;">New Schedule Posted</span>
                        @endif
                    </div>

                    @if(!empty($unreadSchedule))
                        <x-notification-card
                            title="New Schedule Posted"
                            description="Your coach posted a new training schedule for your team. Acknowledge it to keep the update clear."
                            actionUrl="/student/notifications/acknowledge/schedule"
                            actionLabel="Acknowledge"
                        />
                    @endif

                    @if(!empty($latestSchedule))
                        <div style="padding: 18px; border-radius: 22px; background: rgba(255,255,255,0.08); border: 1px solid rgba(212,175,55,0.12);">
                            <strong>{{ date('F j, Y', strtotime($latestSchedule['date'])) }}</strong>
                            <p style="margin: 8px 0 0; color: var(--um-gray);">{{ $latestSchedule['item']['venue'] }} · {{ App\Services\AttendanceStore::formatDisplayTime($latestSchedule['item']['time'] ?? null) }}</p>
                        </div>
                    @else
                        <p>No schedule has been posted yet.</p>
                    @endif
                </div>

                <div class="form-card">
                    <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; margin-bottom:18px;">
                        <h4 style="margin:0;">Latest Announcements</h4>
                        @if(!empty($latestAnnouncement))
                            <span style="display:inline-flex; align-items:center; padding:6px 10px; border-radius:999px; background:rgba(212,175,55,0.14); color:#f4c542; font-size:0.78rem; font-weight:700; text-transform:uppercase; letter-spacing:0.06em;">New</span>
                        @endif
                    </div>
                    @if(!empty($latestAnnouncement))
                        <div style="padding: 18px; border-radius: 22px; background: rgba(255,255,255,0.08); border: 1px solid rgba(212,175,55,0.12);">
                            <strong>{{ $latestAnnouncement['title'] }}</strong>
                            <p style="margin: 8px 0 0; color: var(--um-gray);">{{ $latestAnnouncement['body'] }}</p>
                        </div>
                    @else
                        <p>No announcements yet.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

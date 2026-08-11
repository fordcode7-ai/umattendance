@extends('layouts.app')

@section('title', 'System Settings | UM Attendance')

@section('content')
<div class="page-panel">
    <div class="large-box">
        <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 18px;">
            <div>
                <h2 class="page-title">System Settings</h2>
                <p class="hero-copy">Review student records, manage attendance status, and remove users from the system.</p>
                <p class="hero-copy" style="margin-top: 6px; font-size: 0.95rem; opacity: 0.82;">Attendance tracking begins on <strong>{{ $attendanceStartDateLabel ?? $attendanceStartDate }}</strong>. Records before the system start date are ignored for status calculation.</p>
            </div>
            <form method="GET" class="mobile-form-stack" style="display: flex; gap: 12px; align-items: center;">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search student" class="form-control" style="min-width:240px;" />
                <button class="button button-primary" type="submit">Search</button>
            </form>
        </div>

        <div class="table-grid" style="margin-top: 24px;">
            @foreach($students as $student)
                @php
                    $statusColor = $student['color'] === 'red' ? '#d43945' : ($student['color'] === 'yellow' ? '#f4b400' : '#18a062');
                @endphp
                <div class="student-card" style="border-left: 6px solid {{ $statusColor }};">
                    @if(!empty($student['avatar']))
                        <img src="{{ $student['avatar'] }}" alt="{{ $student['first_name'] }} {{ $student['last_name'] }}" class="avatar avatar-image" />
                    @else
                        <div class="avatar">{{ strtoupper(substr($student['first_name'],0,1)) }}{{ strtoupper(substr($student['last_name'],0,1)) }}</div>
                    @endif
                    <div class="student-info">
                        <h3>{{ $student['first_name'] }} {{ $student['last_name'] }}</h3>
                        <p>{{ $student['student_id'] ?? 'No ID' }} · {{ ucfirst($student['sport'] ?? 'N/A') }} · {{ $student['year_level'] ?? 'N/A' }} · {{ $student['course'] ?? 'N/A' }}</p>
                    </div>
                    <div class="student-status-grid" style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;margin-top:10px;">
                        <div style="padding:12px;border-radius:14px;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08);">
                            <span style="display:block;font-size:0.78rem;color:rgba(247,245,242,0.72);margin-bottom:4px;">Present</span>
                            <strong>{{ $student['counts']['present'] ?? 0 }}</strong>
                        </div>
                        <div style="padding:12px;border-radius:14px;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08);">
                            <span style="display:block;font-size:0.78rem;color:rgba(247,245,242,0.72);margin-bottom:4px;">Late</span>
                            <strong>{{ $student['counts']['late'] ?? 0 }}</strong>
                        </div>
                        <div style="padding:12px;border-radius:14px;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08);">
                            <span style="display:block;font-size:0.78rem;color:rgba(247,245,242,0.72);margin-bottom:4px;">Absent</span>
                            <strong>{{ $student['counts']['absent'] ?? 0 }}</strong>
                        </div>
                        <div style="padding:12px;border-radius:14px;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08);">
                            <span style="display:block;font-size:0.78rem;color:rgba(247,245,242,0.72);margin-bottom:4px;">Excuse</span>
                            <strong>{{ $student['counts']['excuse'] ?? 0 }}</strong>
                        </div>
                    </div>
                    <div class="stat-grid" style="margin-top: 12px; display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
                        <a href="/admin/student/{{ $student['student_id'] }}/edit" class="button button-secondary" style="min-height: auto; padding: 12px 18px;">Edit Status</a>
                        <form method="POST" action="/admin/student/{{ $student['student_id'] }}/delete" style="margin:0;">
                            @csrf
                            <button type="submit" class="button button-absent" style="min-height: auto; padding: 12px 18px;">Delete</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'System Settings | UM Attendance')

@section('content')
<div class="page-panel">
    <div class="large-box">
        <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 18px;">
            <div>
                <h2 class="page-title">System Settings</h2>
                <p class="hero-copy">Review student records, manage attendance status, and remove users from the system.</p>
            </div>
            <form method="GET" class="mobile-form-stack" style="display: flex; gap: 12px; align-items: center;">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search student" class="form-control" style="min-width:240px;" />
                <button class="button button-primary" type="submit">Search</button>
            </form>
        </div>

        <div class="table-grid" style="margin-top: 24px;">
            @foreach($students as $student)
                <div class="student-card" style="border-left: 6px solid {{ $student['sport'] === 'taekwondo' ? '#f4b400' : '#18a062' }};">
                    @if(!empty($student['avatar']))
                        <img src="{{ $student['avatar'] }}" alt="{{ $student['first_name'] }} {{ $student['last_name'] }}" class="avatar avatar-image" />
                    @else
                        <div class="avatar">{{ strtoupper(substr($student['first_name'],0,1)) }}{{ strtoupper(substr($student['last_name'],0,1)) }}</div>
                    @endif
                    <div class="student-info">
                        <h3>{{ $student['first_name'] }} {{ $student['last_name'] }}</h3>
                        <p>{{ $student['student_id'] ?? 'No ID' }} · {{ ucfirst($student['sport'] ?? 'N/A') }} · {{ $student['year_level'] ?? 'N/A' }} · {{ $student['course'] ?? 'N/A' }}</p>
                    </div>
                    <div class="stat-grid">
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

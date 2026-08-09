@extends('layouts.app')

@section('title', 'Edit Student | UM Attendance')

@section('content')
<div class="page-panel" style="max-width: 860px; margin: 0 auto;">
    <div class="large-box">
        <h2 class="page-title">Edit Student Attendance</h2>
        <p class="hero-copy">Update attendance status for a single student on a specific date.</p>
        <form method="POST" action="/admin/student/{{ $student['student_id'] }}/attendance" class="form-panel" style="margin-top: 24px;">
            @csrf
            <div class="field-row">
                <div class="field-group">
                    <label for="date">Date</label>
                    <input id="date" name="date" type="date" value="{{ now()->toDateString() }}" required />
                </div>
                <div class="field-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" required>
                        <option value="present">Present</option>
                        <option value="late">Late</option>
                        <option value="absent">Absent</option>
                        <option value="excuse">Excuse</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="button button-primary">Update Attendance</button>
        </form>
    </div>
</div>
@endsection

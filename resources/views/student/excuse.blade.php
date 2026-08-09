@extends('layouts.app')

@section('title', 'Submit Excuse | UM Attendance')

@section('content')
<div class="page-panel">
    <div class="large-box">
        <h2 class="page-title">Excuse Request</h2>
        <p class="hero-copy">Submit your reason and optional photos for an excuse. Your coach will review it from the coach dashboard.</p>

        <form method="POST" action="/student/excuse" enctype="multipart/form-data" class="form-panel" style="margin-top: 24px;">
            @csrf
            <div class="field-row">
                <div class="field-group">
                    <label for="date">Date of Absence</label>
                    <input id="date" type="date" name="date" value="{{ old('date', now()->toDateString()) }}" required class="form-control" />
                </div>
            </div>
            <div class="field-group">
                <label for="title">Title</label>
                <input id="title" type="text" name="title" value="{{ old('title') }}" placeholder="Short summary of your excuse" required class="form-control" />
            </div>
            <div class="field-group">
                <label for="reason">Reason</label>
                <textarea id="reason" name="reason" placeholder="Explain why you need an excuse" required class="form-textarea">{{ old('reason') }}</textarea>
            </div>
            <div class="field-group">
                <label for="photos">Attach Photos (optional)</label>
                <input id="photos" type="file" name="photos[]" multiple accept="image/*" class="form-control" />
            </div>
            <div class="hero-actions" style="justify-content: flex-start; margin-top: 16px;">
                <button type="submit" class="button button-primary">Send Excuse</button>
                <a href="/student/dashboard" class="button button-secondary">Back to Dashboard</a>
            </div>
        </form>
    </div>
</div>

@if(!empty($unacknowledgedExcuses))
    @php
        $excuseItems = array_map(function ($excuse) {
            return [
                'label' => $excuse['date'].' — '.$excuse['reason'],
                'new' => App\Services\AttendanceStore::isNewItem($excuse['approved_at'] ?? $excuse['submitted_at'] ?? null),
            ];
        }, $unacknowledgedExcuses);
        $excuseDescription = 'Your coach approved the following excuse request'.(count($unacknowledgedExcuses) > 1 ? 's' : '').'. Acknowledge it to clear the bell notification.';
    @endphp

    <x-notification-card
        title="Approved Excuse Request"
        :description="$excuseDescription"
        actionUrl="/student/notifications/acknowledge/excuse"
        actionLabel="Acknowledge All"
        :items="$excuseItems"
    />
@endif
@endsection

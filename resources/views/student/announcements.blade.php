@extends('layouts.app')

@section('title', 'Announcements | UM Attendance')

@section('content')
<div class="page-panel">
    <div class="large-box">
        <div style="display: flex; flex-wrap: wrap; justify-content: space-between; gap: 18px; align-items: start;">
            <div>
                <span class="section-accent">Team Alerts</span>
                <h2 class="page-title">Announcements</h2>
                <p class="hero-copy">Catch up on the latest coach messages, training updates, and schedule notices for your team.</p>
            </div>
        </div>

        @if(!empty($unreadAnnouncements))
            <x-notification-card
                title="New Announcement"
                description="Your coach posted a new announcement. Acknowledge it to clear the bell notification."
                actionUrl="/student/notifications/acknowledge/announcement"
                actionLabel="Acknowledge"
            />
        @endif

        <div class="table-grid" style="margin-top: 28px;">
            @forelse($announcements as $announcement)
                <article class="announcement-card">
                    <div class="announcement-card-header">
                        <div>
                            <span class="announcement-badge">{{ strtoupper($announcement['sport'] === 'all' ? 'All Teams' : $announcement['sport']) }}</span>
                            <h3>{{ $announcement['title'] }}</h3>
                        </div>
                        <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                            @if(App\Services\AttendanceStore::isNewItem($announcement['created_at'] ?? null))
                                <span style="display:inline-flex; align-items:center; gap:6px; padding:6px 10px; border-radius:999px; background:rgba(212,175,55,0.16); color:#f4c542; font-size:0.78rem; font-weight:700; text-transform:uppercase; letter-spacing:0.06em;">New</span>
                            @endif
                            <span class="announcement-time">{{ date('F j, Y g:i A', strtotime($announcement['created_at'])) }}</span>
                        </div>
                    </div>
                    <p class="announcement-body">{{ $announcement['body'] }}</p>
                </article>
            @empty
                <div class="form-card" style="margin-top: 24px;">
                    <p style="margin:0; color: var(--um-offwhite);">No announcements have been posted yet. Check back soon for updates from your coach.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection

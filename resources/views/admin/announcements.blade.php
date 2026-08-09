@extends('layouts.app')

@section('title', 'Admin Announcements | UM Attendance')

@section('content')
<div class="page-panel">
    <div class="large-box">
        <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 18px;">
            <div>
                <h2 class="page-title">Announcements</h2>
                <p class="hero-copy">Create announcements for Taekwondo, Karatedo, or both divisions.</p>
            </div>
        </div>

        <form method="POST" action="/admin/announcements" class="form-panel" style="margin-top: 24px;">
            @csrf
            <div class="field-row">
                <div class="field-group">
                    <label for="sport">Target</label>
                    <select id="sport" name="sport" class="form-select" required>
                        <option value="all">All Sports</option>
                        <option value="taekwondo">Taekwondo</option>
                        <option value="karatedo">Karatedo</option>
                    </select>
                </div>
            </div>
            <div class="field-group">
                <label for="title">Title</label>
                <input id="title" name="title" type="text" class="form-control" required />
            </div>
            <div class="field-group">
                <label for="body">Message</label>
                <textarea id="body" name="body" class="form-textarea" required></textarea>
            </div>
            <button type="submit" class="button button-primary">Post Announcement</button>
        </form>

        <div class="table-grid" style="margin-top: 24px;">
            @foreach($announcements as $announcement)
                <div class="student-card" style="border-left: 6px solid #d4af37;">
                    <h3>{{ $announcement['title'] }}</h3>
                    <p>{{ $announcement['body'] }}</p>
                    <small style="color: var(--um-gray);">Target: {{ ucfirst($announcement['sport']) }} · {{ $announcement['created_at'] }}</small>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

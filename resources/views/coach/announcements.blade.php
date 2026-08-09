@extends('layouts.app')

@section('title', 'Coach Announcements | UM Attendance')

@section('content')
<div class="page-panel">
    <div class="large-box" style="max-width: 1120px; margin: 0 auto;">
        <section class="page-title-row" style="margin-bottom: 30px; align-items: flex-start; gap: 24px;">
            <div style="max-width: 720px;">
                <span class="section-accent">Team Announcements</span>
                <h2 class="page-title" style="margin-top: 18px;">Coach Bulletin</h2>
                <p class="hero-copy">Publish clear, beautiful updates for your Taekwondo or Karatedo athletes. Everything is organized into a clean feed so the team stays aligned.</p>
            </div>
            <div style="min-width: 240px;">
                <div class="hero-copy-strong">Broadcast drills, reminders, and schedule news with elegant cards that keep your team in the loop.</div>
            </div>
        </section>

        <section style="display: grid; gap: 26px;">
            <div style="display: grid; gap: 28px;">
                <div class="form-panel" style="padding: 32px 32px 26px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; gap: 18px; margin-bottom: 22px; flex-wrap: wrap;">
                        <div>
                            <p class="hero-copy-strong" style="margin: 0;">Publish a new bulletin.</p>
                            <p class="hero-copy" style="margin: 10px 0 0; color: rgba(247,245,242,0.72);">Choose the target team and write the message athletes will see in their feed.</p>
                        </div>
                    </div>
                    <form method="POST" action="/coach/announcements" class="announcement-form">
                        @csrf
                        <div class="field-row two-column">
                            <div class="field-group">
                                <label class="form-label" for="title">Title</label>
                                <input id="title" type="text" name="title" required class="form-control" placeholder="Announcement headline" />
                            </div>
                            <div class="field-group">
                                <label class="form-label" for="sport">Target Team</label>
                                <select id="sport" name="sport" class="form-select">
                                    <option value="all">All Teams</option>
                                    <option value="taekwondo">Taekwondo</option>
                                    <option value="karatedo">Karatedo</option>
                                </select>
                            </div>
                        </div>

                        <div class="field-group" style="margin-top: 18px;">
                            <label class="form-label" for="body">Message</label>
                            <textarea id="body" name="body" required rows="6" class="form-control" placeholder="Write something motivating or informative for your team..."></textarea>
                        </div>

                        <div class="button-row" style="justify-content: flex-end; margin-top: 24px;">
                            <button type="submit" class="button button-primary">Publish Announcement</button>
                        </div>
                    </form>
                </div>

                <div style="display: grid; gap: 18px;">
                    @if(count($announcements))
                        @foreach($announcements as $announcement)
                            <article class="announcement-card">
                                <div class="announcement-card-header" style="align-items: flex-start; gap: 18px;">
                                    <div>
                                        <span class="announcement-badge">{{ strtoupper($announcement['sport'] === 'all' ? 'All Teams' : $announcement['sport']) }}</span>
                                        <h3>{{ $announcement['title'] }}</h3>
                                    </div>
                                    <div style="margin-left:auto; display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
                                        <form method="POST" action="/coach/announcements/{{ $announcement['id'] }}/delete" style="margin:0;">
                                            @csrf
                                            <button type="submit" class="button button-danger">Delete</button>
                                        </form>
                                        <details style="min-width: 130px;">
                                            <summary class="button button-present" style="cursor: pointer;">Edit</summary>
                                            <div style="margin-top:16px;">
                                                <form method="POST" action="/coach/announcements/{{ $announcement['id'] }}/update" class="announcement-form">
                                                    @csrf
                                                    <div class="field-row two-column" style="gap: 14px;">
                                                        <div class="field-group">
                                                            <label class="form-label" for="title_{{ $announcement['id'] }}">Title</label>
                                                            <input id="title_{{ $announcement['id'] }}" type="text" name="title" required class="form-control" value="{{ $announcement['title'] }}" />
                                                        </div>
                                                        <div class="field-group">
                                                            <label class="form-label" for="sport_{{ $announcement['id'] }}">Target Team</label>
                                                            <select id="sport_{{ $announcement['id'] }}" name="sport" class="form-select">
                                                                <option value="all" {{ $announcement['sport'] === 'all' ? 'selected' : '' }}>All Teams</option>
                                                                <option value="taekwondo" {{ $announcement['sport'] === 'taekwondo' ? 'selected' : '' }}>Taekwondo</option>
                                                                <option value="karatedo" {{ $announcement['sport'] === 'karatedo' ? 'selected' : '' }}>Karatedo</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="field-group" style="margin-top: 16px;">
                                                        <label class="form-label" for="body_{{ $announcement['id'] }}">Message</label>
                                                        <textarea id="body_{{ $announcement['id'] }}" name="body" required rows="4" class="form-control">{{ $announcement['body'] }}</textarea>
                                                    </div>
                                                    <div class="button-row" style="justify-content: flex-end; margin-top: 18px;">
                                                        <button type="submit" class="button button-present">Save Changes</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </details>
                                    </div>
                                </div>
                                <span class="announcement-time">{{ date('M j, Y g:i a', strtotime($announcement['created_at'])) }}</span>
                                <p class="announcement-body">{{ $announcement['body'] }}</p>
                            </article>
                        @endforeach
                    @else
                        <div class="form-card" style="padding: 30px; text-align: center;">
                            <p style="margin:0; color: var(--um-offwhite); font-size: 1rem;">No announcements posted yet. Create the first message to keep your athletes in sync.</p>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </div>
</div>
@endsection

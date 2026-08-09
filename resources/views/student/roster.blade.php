@extends('layouts.app')

@section('title', 'Team Status | UM Attendance')

@section('content')
<div class="page-panel student-roster">
    <div class="large-box" style="padding: 22px; gap: 20px;">
        <div style="display: flex; flex-wrap: wrap; justify-content: space-between; gap: 18px; align-items: flex-start;">
            <div style="min-width: 240px; flex: 1 1 320px;">
                <h2 class="page-title" style="font-size: clamp(2rem, 3vw, 2.8rem); margin-bottom: 0.6rem;">Team Status</h2>
                <p class="hero-copy" style="max-width: 680px; margin: 0; color: rgba(247, 245, 242, 0.82); font-size: 1rem; line-height: 1.7;">
                    Review monthly performance for every teammate in your division. Use the compact controls to keep the roster clean on mobile.
                </p>
            </div>

            <div class="student-date-panel" style="display:flex;flex-direction:column;gap:10px;width:100%;">
                <form id="month-form" method="GET" action="/student/roster" class="student-date-row">
                    <input type="hidden" name="search" value="{{ $search }}" />
                    <select id="month-select" name="month" class="form-select coach-date-select">
                        @foreach(range(1, 12) as $optionMonth)
                            <option value="{{ $optionMonth }}" {{ $optionMonth === $month ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $optionMonth, 1)) }}</option>
                        @endforeach
                    </select>
                    <select id="year-select" name="year" class="form-select coach-date-select">
                        @foreach([$year - 1, $year, $year + 1] as $optionYear)
                            <option value="{{ $optionYear }}" {{ $optionYear === $year ? 'selected' : '' }}>{{ $optionYear }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="button button-secondary coach-date-button">Go</button>
                </form>

                <div class="student-nav-row" style="display: flex; flex-wrap: wrap; gap: 8px; align-items: center;">
                    <a href="/student/roster?year={{ $prevMonthYear }}&month={{ $prevMonthValue }}" class="button button-secondary coach-nav-button">Prev</a>
                    <a href="/student/roster?year={{ $nextMonthYear }}&month={{ $nextMonthValue }}" class="button button-secondary coach-nav-button">Next</a>
                </div>
            </div>
        </div>

        <div style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 14px; align-items: center; margin-top: 18px;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <button type="button" class="icon-btn" id="search-toggle" aria-label="Toggle teammate search" style="width: 42px; height: 42px; border-radius: 16px; display: inline-flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.12);">
                    <i class="bi bi-search" style="font-size: 1.05rem; color: #f3f3f3;"></i>
                </button>
                <span style="color: rgba(247, 245, 242, 0.88); font-size: 0.95rem;">Search teammate</span>
            </div>

            <div style="display: flex; flex-wrap: wrap; gap: 12px; color: rgba(247, 245, 242, 0.72); font-size: 0.92rem;">
                <span><span class="dot red"></span> 2+ absences</span>
                <span><span class="dot yellow"></span> 3+ lates</span>
                <span><span class="dot green"></span> Good performance</span>
            </div>
        </div>

        <form method="GET" id="search-form" style="display: none; width: 100%; gap: 10px; flex-wrap: wrap; align-items: center; margin-top: 12px;">
            <input type="hidden" name="year" value="{{ $year }}" />
            <input type="hidden" name="month" value="{{ $month }}" />
            <input id="search-input" type="text" name="search" value="{{ $search }}" placeholder="Search teammate" class="form-control" style="flex: 1 1 240px; min-width: 220px; max-width: 100%;" />
            <button class="button button-primary" type="submit" style="min-width: 84px; padding: 12px 16px;">Find</button>
        </form>

        <div class="roster-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px; margin-top: 18px;">
            @forelse($students as $student)
            @php
                $colorHex = $student['color'] === 'red' ? '#d43945' : ($student['color'] === 'yellow' ? '#f4b400' : '#18a062');
                [$r, $g, $b] = sscanf($colorHex, "#%02x%02x%02x");
                $glow = "0 6px 18px rgba($r,$g,$b,0.06)";
                $cardBg = 'rgba(255,255,255,0.03)';
                $borderColor = 'rgba(255,255,255,0.06)';
            @endphp
            <article class="student-card coach-student-card" style="border-radius: 20px; padding: 14px; background: {{ $cardBg }}; border: 1px solid {{ $borderColor }}; box-shadow: 0 2px 8px rgba(0,0,0,0.12), {{ $glow }}; border-left: 6px solid {{ $colorHex }};">
                <div style="display: flex; gap: 10px; align-items: center;">
                    @if(!empty($student['avatar']))
                        <img src="{{ $student['avatar'] }}" alt="{{ $student['first_name'] }} {{ $student['last_name'] }}" class="avatar avatar-image" style="width: 48px; height: 48px; border-radius: 12px; object-fit: cover;" />
                    @else
                        <div class="avatar" style="width: 48px; height: 48px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; background: rgba(255,255,255,0.08); color: #fff;">
                            {{ strtoupper(substr($student['first_name'],0,1)) }}{{ strtoupper(substr($student['last_name'],0,1)) }}
                        </div>
                    @endif
                    <div style="min-width: 0;">
                        <h3 style="margin: 0 0 6px; font-size: 0.98rem; line-height: 1.2;">{{ $student['first_name'] }} {{ $student['last_name'] }}</h3>
                        <p style="margin: 0; color: rgba(247,245,242,0.72); font-size: 0.88rem; line-height: 1.4;">
                            {{ $student['student_id'] }} · {{ ucfirst($student['sport']) }}<br />
                            {{ $student['year_level'] }} · {{ $student['course'] }}
                        </p>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; margin-top: 14px;">
                    <div style="padding: 12px; border-radius: 16px; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08);">
                        <span style="display: block; font-size: 0.72rem; color: rgba(247,245,242,0.68); margin-bottom: 4px;">Present</span>
                        <strong style="font-size: 1rem;">{{ $student['counts']['present'] }}</strong>
                    </div>
                    <div style="padding: 12px; border-radius: 16px; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08);">
                        <span style="display: block; font-size: 0.72rem; color: rgba(247,245,242,0.68); margin-bottom: 4px;">Excuse</span>
                        <strong style="font-size: 1rem;">{{ $student['counts']['excuse'] }}</strong>
                    </div>
                    <div style="padding: 12px; border-radius: 16px; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08);">
                        <span style="display: block; font-size: 0.72rem; color: rgba(247,245,242,0.68); margin-bottom: 4px;">Late</span>
                        <strong style="font-size: 1rem;">{{ $student['counts']['late'] }}</strong>
                    </div>
                    <div style="padding: 12px; border-radius: 16px; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08);">
                        <span style="display: block; font-size: 0.72rem; color: rgba(247,245,242,0.68); margin-bottom: 4px;">Absent</span>
                        <strong style="font-size: 1rem;">{{ $student['counts']['absent'] }}</strong>
                    </div>
                    <div style="grid-column: 1 / -1; padding: 12px; border-radius: 16px; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08);">
                        <span style="display: block; font-size: 0.72rem; color: rgba(247,245,242,0.68); margin-bottom: 4px;">Special Training</span>
                        <strong style="font-size: 1rem;">{{ $student['counts']['special_training'] }}</strong>
                    </div>
                </div>
            </article>
            @empty
            <div style="grid-column: 1 / -1; padding: 18px 16px; border-radius: 20px; background: rgba(255,255,255,0.05); color: rgba(247,245,242,0.82);">
                No teammates were found for this month. Try a different month or clear the search filter.
            </div>
            @endforelse
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var toggle = document.getElementById('search-toggle');
        var form = document.getElementById('search-form');
        var input = document.getElementById('search-input');

        if (toggle && form) {
            toggle.addEventListener('click', function () {
                if (form.style.display === 'none' || form.style.display === '') {
                    form.style.display = 'flex';
                    input.focus();
                } else {
                    form.style.display = 'none';
                }
            });
        }
    });
</script>
@endsection

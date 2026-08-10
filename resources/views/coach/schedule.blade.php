@extends('layouts.app')

@section('title', 'Coach Schedule | UM Attendance')

@section('content')
@php
    $venueOptions = ['OVAL', 'FEA GYM', 'FITNES GYM', 'TAEKWONDO ROOM', 'JUDO ROOM', 'BIG GYM(UM MAIN GYM)'];
@endphp
<div class="page-panel">
    <div class="large-box">
        <h2 class="page-title">{{ ucfirst($sport) }} Schedule</h2>
        <p class="hero-copy">Add or update training dates, times, and venues for your sport with a clear calendar-style layout.</p>

        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-top: 24px;">
            <div style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
                <span style="font-weight:600; color: var(--um-gray);">Month:</span>
                <form method="GET" action="/coach/schedule/{{ $sport }}" style="margin:0; display:flex; gap:12px; align-items:center;">
                    <select name="month" id="month" onchange="this.form.submit()" class="form-control form-select" style="min-width: 240px;">
                        @foreach($availableMonths as $monthValue => $monthLabel)
                            <option value="{{ $monthValue }}" {{ $monthValue === $monthKey ? 'selected' : '' }}>{{ $monthLabel }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <a href="/coach/schedule/{{ $sport }}?month={{ $prevMonth }}" class="button button-secondary">&laquo; Prev</a>
                <a href="/coach/schedule/{{ $sport }}?month={{ $nextMonth }}" class="button button-secondary">Next &raquo;</a>
            </div>
        </div>

        @if(!empty($schedulePublishedAt))
            <div class="page-notice page-notice-success" style="margin-top: 18px;">
                <span class="page-notice-dot"></span>
                <div>
                    <strong>Last posted:</strong>
                    <p>{{ date('F j, Y g:i A', strtotime($schedulePublishedAt)) }}</p>
                </div>
            </div>
        @endif

        <form method="POST" action="/coach/schedule/{{ $sport }}" class="form-panel" style="margin-top: 24px;">
            @csrf
            <input type="hidden" name="month" value="{{ $monthKey }}" />
            <div class="field-row">
                <div class="field-group">
                    <label for="date">Date</label>
                    <input id="date" type="date" name="date" required class="form-control" />
                </div>
                <div class="field-group">
                    <label for="session_type">Session Type</label>
                    <select id="session_type" name="session_type" required class="form-control form-select">
                        <option value="training">Training</option>
                        <option value="no_training">No Training</option>
                    </select>
                </div>
            </div>
            <div class="field-row">
                <div class="field-group">
                    <label for="time">Time</label>
                    <input id="time" type="text" name="time" placeholder="8:00 AM - 10:00 AM" class="form-control" />
                </div>
                <div class="field-group">
                    <label for="venue">Venue</label>
                    <select id="venue" name="venue" class="form-control form-select">
                        <option value="" disabled selected>Select a venue</option>
                        @foreach($venueOptions as $venueOption)
                            <option value="{{ $venueOption }}">{{ $venueOption }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="action-row" style="margin-top:12px; display:flex; gap:12px; justify-content:flex-end;">
                <button class="button button-primary" type="submit">Save Schedule</button>
            </div>
        </form>

        @php
            $selectedMonth = \DateTime::createFromFormat('!Y-m', $monthKey) ?: new \DateTime();
            $firstOfMonth = strtotime($selectedMonth->format('Y-m-01'));
            $daysInMonth = date('t', $firstOfMonth);
        @endphp

        <div class="month-grid" style="margin-top: 26px;">
            @for($day = 1; $day <= $daysInMonth; $day++)
                @php
                    $date = sprintf('%s-%02d', $selectedMonth->format('Y-m'), $day);
                    $item = $schedules[$date] ?? null;
                    $dayName = date('D', strtotime($date));
                    $isSunday = $dayName === 'Sun';
                @endphp
                <div class="calendar-cell month-cell {{ $item ? 'has-schedule' : ($isSunday ? 'rest-day' : 'empty-day') }}">
                    <div>
                        <h4>{{ $day }} <span style="color: var(--um-gray); font-size: 0.95rem;">{{ $dayName }}</span></h4>
                        <p>{{ date('M j, Y', strtotime($date)) }}</p>
                    </div>
                    <div style="margin-top: 12px; display:grid; gap:8px;">
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
                    <div class="schedule-actions action-row" style="margin-top: 14px; display: flex; justify-content: flex-end; gap: 8px; flex-wrap: wrap;">
                        @if($item)
                            <details style="min-width: 150px;">
                                <summary class="button button-present" style="cursor: pointer;">Edit</summary>
                                <form method="POST" action="/coach/schedule/{{ $sport }}/{{ $date }}/update" class="form-panel" style="margin-top: 12px;">
                                    @csrf
                                    <input type="hidden" name="month" value="{{ $monthKey }}" />
                                    @php
                                        $currentType = ($item['time'] ?? '') === 'No Training' ? 'no_training' : 'training';
                                    @endphp
                                    <div class="field-row">
                                        <div class="field-group">
                                            <label for="date_{{ $date }}">Date</label>
                                            <input id="date_{{ $date }}" type="date" name="date" value="{{ $date }}" required class="form-control" />
                                        </div>
                                        <div class="field-group">
                                            <label for="session_type_{{ $date }}">Session Type</label>
                                            <select id="session_type_{{ $date }}" name="session_type" required class="form-control form-select">
                                                <option value="training" {{ $currentType === 'training' ? 'selected' : '' }}>Training</option>
                                                <option value="no_training" {{ $currentType === 'no_training' ? 'selected' : '' }}>No Training</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="field-row">
                                        <div class="field-group">
                                            <label for="time_{{ $date }}">Time</label>
                                            <input id="time_{{ $date }}" type="text" name="time" value="{{ $currentType === 'training' ? $item['time'] : '' }}" class="form-control" />
                                        </div>
                                        <div class="field-group">
                                            <label for="venue_{{ $date }}">Venue</label>
                                            <select id="venue_{{ $date }}" name="venue" class="form-control form-select">
                                                <option value="" disabled>Select a venue</option>
                                                @foreach($venueOptions as $venueOption)
                                                    <option value="{{ $venueOption }}" {{ ($item['venue'] ?? '') === $venueOption ? 'selected' : '' }}>{{ $venueOption }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="action-row" style="margin-top:8px; display:flex; gap:8px; justify-content:flex-end;">
                                        <button type="submit" class="button button-primary">Save</button>
                                    </div>
                                </form>
                            </details>

                            <form method="POST" action="/coach/schedule/{{ $sport }}/{{ $date }}/delete" style="margin: 0;">
                                @csrf
                                <button type="submit" class="button button-danger">Delete</button>
                            </form>
                        @endif
                    </div>
                </div>
            @endfor
            </div>

            <form method="POST" action="/coach/schedule/{{ $sport }}/publish" style="margin-top: 20px; display:flex; gap: 12px; flex-wrap:wrap; align-items:center;">
                @csrf
                <input type="hidden" name="month" value="{{ $monthKey }}" />
                <button class="button button-primary" type="submit" {{ $pendingPublish ? '' : 'disabled' }}>Post Schedule to Students</button>
                @if(!$pendingPublish)
                    <span style="color: var(--um-gray);">No new schedule updates pending post.</span>
                @endif
            </form>
    </div>
</div>
@endsection

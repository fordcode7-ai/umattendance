@extends('layouts.app')

@section('title', 'Coaches | UM Attendance')

@section('content')
<div class="page-panel">
    <div class="large-box">
        <div style="display:flex; justify-content:space-between; align-items:center; gap:12px;">
            <div>
                <h2 class="page-title">Coaches</h2>
                <p class="hero-copy">List of coach accounts in the system.</p>
            </div>
            <a href="/admin/create-coach" class="button button-primary">Create Coach</a>
        </div>

        @php
            $taekwondo = [];
            $karatedo = [];
            foreach ($coaches as $c) {
                $sport = strtolower(trim($c['sport'] ?? ''));
                if ($sport === 'taekwondo' || $sport === 'all') { $taekwondo[] = $c; }
                if ($sport === 'karatedo' || $sport === 'all') { $karatedo[] = $c; }
            }
        @endphp

        <div class="coach-columns">
            <div class="coach-column">
                <h4 class="coach-column-title">Taekwondo</h4>
                @if(count($taekwondo))
                    @foreach($taekwondo as $coach)
                        <div class="coach-card">
                            <div class="coach-left">
                                @if(!empty($coach['avatar']))
                                    <img src="{{ $coach['avatar'] }}" alt="avatar" class="coach-avatar" />
                                @else
                                    <div class="coach-initials">{{ strtoupper(substr($coach['first_name'] ?? '', 0, 1) . substr($coach['last_name'] ?? '', 0, 1)) }}</div>
                                @endif
                                <div class="coach-meta">
                                    <div class="coach-name">{{ $coach['first_name'] }} {{ $coach['last_name'] }}</div>
                                    <div class="coach-sub">{{ $coach['email'] }} · {{ $coach['sport'] }}</div>
                                </div>
                            </div>
                            <div class="coach-actions">
                                <a href="/admin/coach/{{ $coach['id'] }}/edit" class="button button-secondary">Edit</a>
                                <form method="POST" action="/admin/coach/{{ $coach['id'] }}/delete" class="confirm-delete" data-name="{{ $coach['first_name'] }} {{ $coach['last_name'] }}">
                                    @csrf
                                    <button type="submit" class="button button-danger">Delete</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="form-card" style="margin-top:12px;"><p>No Taekwondo coaches.</p></div>
                @endif
            </div>

            <div class="coach-column">
                <h4 class="coach-column-title">Karatedo</h4>
                @if(count($karatedo))
                    @foreach($karatedo as $coach)
                        <div class="coach-card">
                            <div class="coach-left">
                                @if(!empty($coach['avatar']))
                                    <img src="{{ $coach['avatar'] }}" alt="avatar" class="coach-avatar" />
                                @else
                                    <div class="coach-initials">{{ strtoupper(substr($coach['first_name'] ?? '', 0, 1) . substr($coach['last_name'] ?? '', 0, 1)) }}</div>
                                @endif
                                <div class="coach-meta">
                                    <div class="coach-name">{{ $coach['first_name'] }} {{ $coach['last_name'] }}</div>
                                    <div class="coach-sub">{{ $coach['email'] }} · {{ $coach['sport'] }}</div>
                                </div>
                            </div>
                            <div class="coach-actions">
                                <a href="/admin/coach/{{ $coach['id'] }}/edit" class="button button-secondary">Edit</a>
                                <form method="POST" action="/admin/coach/{{ $coach['id'] }}/delete" class="confirm-delete" data-name="{{ $coach['first_name'] }} {{ $coach['last_name'] }}">
                                    @csrf
                                    <button type="submit" class="button button-danger">Delete</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="form-card" style="margin-top:12px;"><p>No Karatedo coaches.</p></div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

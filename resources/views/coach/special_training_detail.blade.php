@extends('layouts.app')

@section('title', 'Special Training Request Details | UM Attendance')

@section('content')
<div class="page-panel">
    <div class="large-box">
        <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 18px; margin-bottom: 22px;">
            <div>
                <h2 class="page-title">Special Training Request Details</h2>
                <p class="hero-copy">Review the request information and approve it when you are ready.</p>
            </div>
            <a href="/coach/special-training" class="button button-secondary">Back to requests</a>
        </div>

        <div class="form-card" style="padding: 32px 32px 24px; background: linear-gradient(180deg, rgba(255,255,255,0.06), rgba(255,255,255,0.04)); border: 1px solid rgba(255,255,255,0.12); box-shadow: 0 32px 60px rgba(0, 0, 0, 0.18);">
            <div style="display: grid; grid-template-columns: minmax(260px, 1fr) auto; gap: 24px; align-items: start;">
                <div style="display: grid; gap: 14px;">
                    <div style="display: grid; gap: 8px;">
                        <span style="font-size: 0.85rem; letter-spacing: 0.12em; text-transform: uppercase; color: rgba(212, 175, 55, 0.92);">Request summary</span>
                        <h3 style="margin: 0; font-size: 1.5rem;">{{ $request['title'] ?? 'Special Training Request' }}</h3>
                    </div>
                    <div style="display: grid; gap: 4px; color: var(--um-gray);">
                        <div><strong>Sender:</strong> {{ $request['student_display'] ?? 'Student ID: '.$request['student_id'] }}</div>
                        <div><strong>Date range:</strong> {{ $request['date_range'] ?? ($request['start_date'].' to '.$request['end_date']) }}</div>
                        @if(!empty($request['sport']))
                            <div><strong>Sport:</strong> {{ ucfirst($request['sport']) }}</div>
                        @endif
                        @if(!empty($request['submitted_at_label']))
                            <div><strong>Submitted:</strong> {{ $request['submitted_at_label'] }}</div>
                        @endif
                    </div>
                </div>
                <div style="display: flex; justify-content: flex-end; align-items: start;">
                    <form method="POST" action="/coach/special-training/{{ $request['id'] }}/approve" style="margin: 0;">
                        @csrf
                        <button type="submit" class="button button-primary" style="padding: 16px 24px; min-width: 180px; font-size: 1rem;">Approve request</button>
                    </form>
                </div>
            </div>

            <div style="margin-top: 32px; padding: 28px; border-radius: 24px; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08);">
                <h3 style="margin: 0 0 14px; font-size: 1.15rem; color: var(--um-white);">Reason for request</h3>
                <p style="margin: 0; color: var(--um-offwhite); font-size: 1rem; line-height: 1.8;">{{ $request['reason'] ?? 'No reason provided.' }}</p>
            </div>

            @if(count($request['attachments'] ?? []))
                <div style="margin-top: 28px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 14px;">
                        <h3 style="margin: 0; font-size: 1.15rem; color: var(--um-white);">Supporting attachments</h3>
                        <span style="color: var(--um-gray); font-size: 0.95rem;">{{ count($request['attachments']) }} attachment{{ count($request['attachments']) > 1 ? 's' : '' }}</span>
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 18px;">
                        @foreach($request['attachments'] as $file)
                            <a href="{{ asset('storage/' . $file) }}" target="_blank" rel="noopener noreferrer" style="display: block; border-radius: 20px; overflow: hidden; background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1); box-shadow: 0 18px 36px rgba(0,0,0,0.16); text-decoration: none; color: inherit;">
                                <img src="{{ asset('storage/' . $file) }}" alt="Special training attachment" style="display: block; width: 100%; height: 140px; object-fit: cover;" />
                                <div style="padding: 14px; font-size: 0.9rem; line-height: 1.4; color: var(--um-offwhite);">{{ basename($file) }}</div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

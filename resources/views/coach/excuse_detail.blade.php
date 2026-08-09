@extends('layouts.app')

@section('title', 'Excuse Request Details | UM Attendance')

@section('content')
<div class="page-panel">
    <div class="large-box">
        <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 18px; margin-bottom: 22px;">
            <div>
                <h2 class="page-title">Excuse Request Details</h2>
                <p class="hero-copy">Review the request and approve the excuse if the reason is valid.</p>
            </div>
            <a href="/coach/excuses" class="button button-secondary">Back to requests</a>
        </div>

        <div class="form-card" style="padding: 32px 32px 24px; background: linear-gradient(180deg, rgba(255,255,255,0.06), rgba(255,255,255,0.04)); border: 1px solid rgba(255,255,255,0.12); box-shadow: 0 32px 60px rgba(0, 0, 0, 0.18);">
            <div style="display: grid; grid-template-columns: minmax(260px, 1fr) auto; gap: 24px; align-items: start;">
                <div style="display: grid; gap: 14px;">
                    <div style="display: grid; gap: 8px;">
                        <span style="font-size: 0.85rem; letter-spacing: 0.12em; text-transform: uppercase; color: rgba(212, 175, 55, 0.92);">Excuse summary</span>
                        <h3 style="margin: 0; font-size: 1.5rem;">{{ $excuse['title'] ?? 'Excuse Request' }}</h3>
                    </div>
                    <div style="display: grid; gap: 4px; color: var(--um-gray);">
                        <div><strong>Sender:</strong> {{ $excuse['student_display'] ?? 'Student ID: '.$excuse['student_id'] }}</div>
                        <div><strong>Date:</strong> {{ $excuse['date'] ?? 'N/A' }}</div>
                        @if(!empty($excuse['sport']))
                            <div><strong>Sport:</strong> {{ ucfirst($excuse['sport']) }}</div>
                        @endif
                        @if(!empty($excuse['submitted_at_label']))
                            <div><strong>Submitted:</strong> {{ $excuse['submitted_at_label'] }}</div>
                        @endif
                    </div>
                </div>
                <div style="display: flex; justify-content: flex-end; align-items: start;">
                    <form method="POST" action="/coach/excuses/{{ $excuse['id'] }}/approve" style="margin: 0;">
                        @csrf
                        <button type="submit" class="button button-primary" style="padding: 16px 24px; min-width: 180px; font-size: 1rem;">Approve excuse</button>
                    </form>
                </div>
            </div>

            <div style="margin-top: 32px; padding: 28px; border-radius: 24px; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08);">
                <h3 style="margin: 0 0 14px; font-size: 1.15rem; color: var(--um-white);">Reason</h3>
                <p style="margin: 0; color: var(--um-offwhite); font-size: 1rem; line-height: 1.8;">{{ $excuse['reason'] ?? 'No reason provided.' }}</p>
            </div>

            @if(count($excuse['attachments'] ?? []))
                <div style="margin-top: 28px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 14px;">
                        <h3 style="margin: 0; font-size: 1.15rem; color: var(--um-white);">Attachments</h3>
                        <span style="color: var(--um-gray); font-size: 0.95rem;">{{ count($excuse['attachments']) }} file{{ count($excuse['attachments']) > 1 ? 's' : '' }}</span>
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 18px;">
                        @foreach($excuse['attachments'] as $file)
                            <a href="{{ asset('storage/' . $file) }}" target="_blank" rel="noopener noreferrer" style="display: block; border-radius: 20px; overflow: hidden; background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1); box-shadow: 0 18px 36px rgba(0,0,0,0.16); text-decoration: none; color: inherit;">
                                <img src="{{ asset('storage/' . $file) }}" alt="Excuse attachment" style="display: block; width: 100%; height: 140px; object-fit: cover;" />
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

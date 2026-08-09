@extends('layouts.app')

@section('title', 'Admin Special Training Requests | UM Attendance')

@section('content')
<div class="page-panel">
    <div class="large-box">
        <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 18px;">
            <div>
                <h2 class="page-title">Special Training Requests</h2>
                <p class="hero-copy">Review special training requests across all sports and approve as needed.</p>
            </div>
        </div>

        <div class="table-grid" style="margin-top: 24px;">
            @if(count($requests))
                @foreach($requests as $request)
                    <div class="form-card" style="padding: 24px; border: 1px solid rgba(255,255,255,0.12); box-shadow: 0 18px 36px rgba(0,0,0,0.18);">
                        <div style="display: grid; grid-template-columns: minmax(240px, 1fr) auto; gap: 20px; align-items: start;">
                            <div style="display: grid; gap: 10px;">
                                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                                    <span style="background: rgba(212,175,55,0.12); color: var(--um-gold); padding: 6px 12px; border-radius: 999px; font-size: 0.80rem; text-transform: uppercase; letter-spacing: 0.08em;">Title</span>
                                    <h3 style="margin: 0; font-size: 1.3rem; line-height: 1.2;">{{ $request['title'] ?? 'Special Training Request' }}</h3>
                                </div>
                                <p style="margin: 0; color: var(--um-gray);"><strong>Sender:</strong> {{ $request['student_display'] ?? 'Student ID: '.$request['student_id'] }}</p>
                                <p style="margin: 0; color: var(--um-gray); font-size: 0.94rem;">{{ $request['date_range'] ?? ($request['start_date'].' to '.$request['end_date']) }} @if(!empty($request['sport'])) · {{ ucfirst($request['sport']) }} @endif</p>
                                @if(!empty($request['submitted_at_label']))
                                    <p style="margin: 4px 0 0; color: var(--um-gray); font-size: 0.84rem;">Submitted {{ $request['submitted_at_label'] }}</p>
                                @endif
                            </div>
                            <div style="display: flex; flex-direction: column; justify-content: flex-start; gap: 12px; align-items: flex-end;">
                                <a href="/admin/special-training/{{ $request['id'] }}" class="button button-secondary" style="text-decoration:none; padding: 12px 18px; min-width: 140px;">View details</a>
                                <form method="POST" action="/admin/special-training/{{ $request['id'] }}/approve" style="margin: 0; width: 100%; display: flex; justify-content: flex-end;">
                                    @csrf
                                    <button type="submit" class="button button-primary" style="padding: 12px 18px; min-width: 140px;">Approve</button>
                                </form>
                            </div>
                        </div>
                        <div style="margin-top: 20px; padding: 20px; border-radius: 22px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.08);">
                            <p style="margin: 0 0 8px; color: var(--um-white); font-weight: 700;">Reason</p>
                            <p style="margin: 0; color: var(--um-offwhite); line-height: 1.7;">{{ $request['reason'] ?? 'No reason provided.' }}</p>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="form-card" style="margin-top: 24px;">
                    <p>No pending special training requests.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Edit Coach | UM Attendance')

@section('content')
<div class="page-panel">
    <div class="large-box">
        <h2 class="page-title">Edit Coach Account</h2>
        <p class="hero-copy">Update coach details below.</p>

        <form method="POST" action="/admin/coach/{{ $coach['id'] }}/update" class="form-panel" enctype="multipart/form-data">
            @csrf
            <div class="coach-edit-header">
                @if(!empty($coach['avatar']))
                    <img src="{{ $coach['avatar'] }}" alt="avatar" class="coach-avatar" />
                @else
                    <div class="coach-initials">{{ strtoupper(substr($coach['first_name'] ?? '', 0, 1) . substr($coach['last_name'] ?? '', 0, 1)) }}</div>
                @endif
                <div class="meta">
                    <strong>{{ $coach['first_name'] }} {{ $coach['last_name'] }}</strong>
                    <div class="coach-edit-email">{{ $coach['email'] }}</div>
                </div>
            </div>
            <div class="field-row">
                <div class="field-group">
                    <label for="first_name">First Name</label>
                    <input id="first_name" name="first_name" type="text" value="{{ $coach['first_name'] }}" required class="form-control" />
                </div>
                <div class="field-group">
                    <label for="middle_name">Middle Initial (optional)</label>
                    <input id="middle_name" name="middle_name" type="text" value="{{ $coach['middle_name'] ?? '' }}" class="form-control" />
                </div>
            </div>
            <div class="field-row">
                <div class="field-group">
                    <label for="last_name">Last Name</label>
                    <input id="last_name" name="last_name" type="text" value="{{ $coach['last_name'] }}" required class="form-control" />
                </div>

                 <div class="field-row">
                <div class="field-group">
                    <label for="sport">Sport</label>
                    <select id="sport" name="sport" required class="form-control">
                        <option value="taekwondo" {{ ($coach['sport'] ?? '') === 'taekwondo' ? 'selected' : '' }}>Taekwondo</option>
                        <option value="karatedo" {{ ($coach['sport'] ?? '') === 'karatedo' ? 'selected' : '' }}>Karatedo</option>
                        <option value="all" {{ ($coach['sport'] ?? '') === 'all' ? 'selected' : '' }}>All</option>
                    </select>
                </div>
                <div class="field-group"></div>
            </div>
                




                <div class="field-group">
                    <label for="contact">Contact Number</label>
                    <input id="contact" name="contact" type="text" value="{{ $coach['contact'] ?? '' }}" class="form-control" />
                </div>
            </div>

            <div class="field-row">
                <div class="field-group">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ $coach['email'] }}" required class="form-control" />
                </div>
                <div class="field-group">
                    <label for="password">Password (leave blank to keep)</label>
                    <div class="password-wrapper">
                        <input id="password" name="password" type="password" class="form-control" />
                        <button type="button" class="button password-toggle" aria-label="Show password"><i class="bi bi-eye"></i></button>
                    </div>
                </div>
            </div>

            <div class="field-row">
                <div class="field-group">
                    <label for="password_confirmation">Confirm Password</label>
                    <div class="password-wrapper">
                        <input id="password_confirmation" name="password_confirmation" type="password" class="form-control" />
                        <button type="button" class="button password-toggle" aria-label="Show password"><i class="bi bi-eye"></i></button>
                    </div>
                </div>
                <div class="field-group">
                    <label for="avatar">Profile Photo (optional)</label>
                    <div class="file-input">
                        <input id="avatar" name="avatar" type="file" accept="image/*" class="form-file visually-hidden" />
                        <button type="button" class="button file-choose">Choose File</button>
                        <span class="file-name">No file chosen</span>
                    </div>
                </div>
            </div>

           

            <div style="display:flex; gap:12px; align-items:center; margin-top:12px;">
                <button type="submit" class="button button-primary">Save Changes</button>
                <a href="/admin/coaches" class="button button-secondary">Cancel</a>
            </div>
        </form>

        <form method="POST" action="/admin/coach/{{ $coach['id'] }}/delete" class="confirm-delete" data-name="{{ $coach['first_name'] }} {{ $coach['last_name'] }}">
            @csrf
            <button type="submit" class="button button-danger">Delete Coach</button>
        </form>
    </div>
</div>
@endsection

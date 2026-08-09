@extends('layouts.app')

@section('title', $user['role'] === 'coach' ? 'Coach Profile | UM Attendance' : 'My Profile | UM Attendance')

@section('content')
<div class="page-panel">
    @php
        $dashboardUrl = match($user['role']) {
            'coach' => '/coach/dashboard',
            'admin' => '/admin/dashboard',
            default => '/student/dashboard',
        };
        $coachSports = [];
        if ($user['role'] === 'coach') {
            $coachSports = is_array($user['sports_handled'] ?? null)
                ? $user['sports_handled']
                : array_filter(explode(',', $user['sports_handled'] ?? $user['sport'] ?? ''));
        }
        $sportsLabel = $user['role'] === 'coach'
            ? implode(' & ', array_map(fn($sport) => ucfirst($sport), $coachSports ?: (array_filter([$user['sport']]) ?: [])))
            : ucfirst($user['sport'] ?? '');
        $memberSince = !empty($user['created_at']) ? date('F Y', strtotime($user['created_at'])) : 'N/A';
        $coachId = strtoupper(str_replace('_', '-', $user['id'] ?? ''));
    @endphp

    <div class="page-title-row" style="display: flex; align-items: center; justify-content: space-between; gap: 18px; flex-wrap: wrap;">
        <div>
            <h1 class="page-title">{{ $user['role'] === 'coach' ? 'Coach Profile' : 'My Profile' }}</h1>
            <p class="hero-copy">Update your profile details and keep your coach profile polished and professional.</p>
        </div>
        <a href="{{ $dashboardUrl }}" class="button button-secondary">Back to Dashboard</a>
    </div>

    <div class="large-box" style="margin-top: 28px;">
        @if(session('success'))
            <div class="alert success">
                <strong>Success</strong>
                <p>{{ session('success') }}</p>
            </div>
        @endif

        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
            @csrf
            <div class="profile-form-grid">
                <div class="profile-fields">
                    <div class="profile-panel">
                        <div style="display: grid; gap: 18px;">
                            <div>
                                <h3 style="margin:0 0 8px;">Personal Information</h3>
                                <p style="margin:0 0 18px; color: var(--um-gray);">Update your name, contact details, and coaching assignments.</p>
                            </div>
                            <div>
                                <label class="form-label">First Name</label>
                                <input type="text" name="first_name" value="{{ old('first_name', $user['first_name']) }}" class="form-input" required />
                            </div>
                            <div>
                                <label class="form-label">Middle Name</label>
                                <input type="text" name="middle_name" value="{{ old('middle_name', $user['middle_name']) }}" class="form-input" />
                            </div>
                            <div>
                                <label class="form-label">Last Name</label>
                                <input type="text" name="last_name" value="{{ old('last_name', $user['last_name']) }}" class="form-input" required />
                            </div>

                            @if($user['role'] === 'coach')
                                <div>
                                    <label class="form-label">Position / Title</label>
                                    <input type="text" name="title" value="{{ old('title', $user['title'] ?? '') }}" class="form-input" placeholder="Head Coach, Assistant Coach, Coach" />
                                </div>
                                <div>
                                    <label class="form-label">Sports Handled</label>
                                    <div class="input-group" style="gap: 12px; flex-wrap: wrap;">
                                        <label class="checkbox-label">
                                            <input type="checkbox" name="sports_handled[]" value="taekwondo" {{ in_array('taekwondo', old('sports_handled', $coachSports), true) ? 'checked' : '' }} />
                                            Taekwondo
                                        </label>
                                        <label class="checkbox-label">
                                            <input type="checkbox" name="sports_handled[]" value="karatedo" {{ in_array('karatedo', old('sports_handled', $coachSports), true) ? 'checked' : '' }} />
                                            Karatedo
                                        </label>
                                    </div>
                                </div>
                                <div>
                                    <label class="form-label">Contact Number</label>
                                    <input type="text" name="contact" value="{{ old('contact', $user['contact']) }}" class="form-input" required />
                                </div>
                                <div>
                                    <label class="form-label">Email Address</label>
                                    <input type="email" name="email" value="{{ old('email', $user['email']) }}" class="form-input" required />
                                </div>
                            @else
                                <div>
                                    <label class="form-label">Year Level</label>
                                    <input type="text" name="year_level" value="{{ old('year_level', $user['year_level']) }}" class="form-input" required />
                                </div>
                                <div>
                                    <label class="form-label">Course</label>
                                    <input type="text" name="course" value="{{ old('course', $user['course']) }}" class="form-input" required />
                                </div>
                                <div>
                                    <label class="form-label">Contact Number</label>
                                    <input type="text" name="contact" value="{{ old('contact', $user['contact']) }}" class="form-input" required />
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                    <div class="profile-meta-card" style="margin-top:18px;">
                        <h3>Change Password</h3>
                        <div class="profile-meta-detail" style="display:grid; gap:12px;">
                            <div>
                                <label class="form-label">Current Password</label>
                                <div class="password-wrapper">
                                    <input type="password" name="current_password" class="form-input" placeholder="Current password" />
                                    <button type="button" class="button password-toggle" aria-label="Show password"><i class="bi bi-eye"></i></button>
                                </div>
                            </div>
                            <div>
                                <label class="form-label">New Password</label>
                                <div class="password-wrapper">
                                    <input type="password" name="password" class="form-input" placeholder="New password" />
                                    <button type="button" class="button password-toggle" aria-label="Show password"><i class="bi bi-eye"></i></button>
                                </div>
                            </div>
                            <div>
                                <label class="form-label">Confirm New Password</label>
                                <div class="password-wrapper">
                                    <input type="password" name="password_confirmation" class="form-input" placeholder="Confirm new password" />
                                    <button type="button" class="button password-toggle" aria-label="Show password"><i class="bi bi-eye"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>

                <div class="profile-side-panel">
                    <div class="profile-picture-card" style="padding-top: 36px; padding-bottom: 26px;">
                        <h3>Profile Picture</h3>
                        @if(!empty($user['avatar']))
                            <img src="{{ $user['avatar'] }}" alt="Profile Image" class="avatar avatar-image" style="width: 140px; height: 140px; border-radius: 50%; object-fit: cover;" />
                        @else
                            <div class="avatar" style="font-size: 2.4rem; width: 140px; height: 140px; border-radius: 50%; display: grid; place-items: center;">{{ strtoupper(substr($user['first_name'], 0, 1).substr($user['last_name'], 0, 1)) }}</div>
                        @endif
                        <label for="avatar" class="button button-secondary" style="width: 100%; margin-top: 8px;">
                            Change Photo / Choose File
                        </label>
                        <input type="file" id="avatar" name="avatar" accept="image/*" class="form-file" style="display:none;" />
                        <p style="margin: 0; color: var(--um-gray); font-size: 0.95rem; text-align: center;">Use a clear photo for your coach profile.</p>
                    </div>

                    <div class="profile-meta-card">
                        <h3>Account Details</h3>
                        @if($user['role'] === 'coach')
                            <div class="profile-meta-detail">
                                <span class="profile-meta-label">Coach ID</span>
                                <span class="profile-meta-value">{{ $coachId }}</span>
                            </div>
                            <div class="profile-meta-detail">
                                <span class="profile-meta-label">Role</span>
                                <span class="profile-meta-value">{{ ucfirst($user['role']) }}</span>
                            </div>
                            <div class="profile-meta-detail">
                                <span class="profile-meta-label">Sports Handled</span>
                                <span class="profile-meta-value">{{ $sportsLabel ?: 'Not assigned' }}</span>
                            </div>
                            <div class="profile-meta-detail">
                                <span class="profile-meta-label">Status</span>
                                <span class="profile-meta-value" style="color: #D4AF37;">● Active</span>
                            </div>
                            <div class="profile-meta-detail" style="border-bottom: none;">
                                <span class="profile-meta-label">Member Since</span>
                                <span class="profile-meta-value">{{ $memberSince }}</span>
                            </div>
                        @else
                            <div class="profile-meta-detail">
                                <span class="profile-meta-label">Student ID</span>
                                <span class="profile-meta-value">{{ $user['student_id'] }}</span>
                            </div>
                            <div class="profile-meta-detail">
                                <span class="profile-meta-label">Role</span>
                                <span class="profile-meta-value">{{ ucfirst($user['role']) }}</span>
                            </div>
                            <div class="profile-meta-detail" style="border-bottom: none;">
                                <span class="profile-meta-label">Sport</span>
                                <span class="profile-meta-value">{{ ucfirst($user['sport'] ?? '') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="button-row">
                <button type="submit" class="button button-primary">Save Changes</button>
                <a href="{{ $dashboardUrl }}" class="button button-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

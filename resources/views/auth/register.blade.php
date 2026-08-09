@extends('layouts.app')

@section('title', 'Register | UM Attendance')

@section('content')
<div class="public-form-panel register-public-form-panel">
    <div class="public-form-box register-form-box">
        <div class="landing-pill">UM</div>
        <div class="public-form-heading">
            <p class="landing-badge">Create Student Account</p>
            <h1 class="page-title" style="font-size: clamp(2.6rem, 4vw, 3.4rem);">Register as a Student Athlete</h1>
            <p class="landing-subtitle">Complete the form below to join the UM Taekwondo &amp; Karatedo attendance platform.</p>
        </div>

        <form method="POST" action="/register" class="form-panel public-login-form" style="margin-top: 36px;">
            @csrf
            <div class="section-card">
                <div class="form-section-header">
                    <div class="section-icon"><i class="bi bi-person-lines-fill"></i></div>
                    <h3>Personal Information</h3>
                </div>
                <div class="field-row">
                    <div class="field-group">
                        <label for="student_id">Student ID</label>
                        <input id="student_id" type="text" name="student_id" value="{{ old('student_id') }}" required placeholder="UM123456" class="form-control" />
                    </div>
                    <div class="field-group">
                        <label for="first_name">First Name</label>
                        <input id="first_name" type="text" name="first_name" value="{{ old('first_name') }}" required class="form-control" />
                    </div>
                </div>
                <div class="field-row">
                    <div class="field-group">
                        <label for="middle_name">Middle Initial</label>
                        <input id="middle_name" type="text" name="middle_name" value="{{ old('middle_name') }}" placeholder="J." class="form-control" />
                    </div>
                    <div class="field-group">
                        <label for="last_name">Last Name</label>
                        <input id="last_name" type="text" name="last_name" value="{{ old('last_name') }}" required class="form-control" />
                    </div>
                </div>
            </div>

            <div class="section-card">
                <div class="form-section-header">
                    <div class="section-icon"><i class="bi bi-mortarboard-fill"></i></div>
                    <h3>Academic Information</h3>
                </div>
                <div class="field-row">
                    <div class="field-group">
                        <label for="year_level">Year Level</label>
                        <select id="year_level" name="year_level" required class="form-select">
                            <option value="">Select year level</option>
                            <option value="1st Year" {{ old('year_level') === '1st Year' ? 'selected' : '' }}>1st Year</option>
                            <option value="2nd Year" {{ old('year_level') === '2nd Year' ? 'selected' : '' }}>2nd Year</option>
                            <option value="3rd Year" {{ old('year_level') === '3rd Year' ? 'selected' : '' }}>3rd Year</option>
                            <option value="4th Year" {{ old('year_level') === '4th Year' ? 'selected' : '' }}>4th Year</option>
                        </select>
                    </div>
                    <div class="field-group">
                        <label for="course">Course</label>
                        <input id="course" type="text" name="course" value="{{ old('course') }}" required placeholder="BSIT" class="form-control" />
                    </div>
                </div>
            </div>

            <div class="section-card">
                <div class="form-section-header">
                    <div class="section-icon"><i class="bi bi-award-fill"></i></div>
                    <h3>Athlete Information</h3>
                </div>
                <div class="field-row">
                    <div class="field-group">
                        <label for="sport">Sport Division</label>
                        <select id="sport" name="sport" required class="form-select">
                            <option value="">Choose sport</option>
                            <option value="taekwondo" {{ old('sport') === 'taekwondo' ? 'selected' : '' }}>Taekwondo</option>
                            <option value="karatedo" {{ old('sport') === 'karatedo' ? 'selected' : '' }}>Karatedo</option>
                        </select>
                    </div>
                    <div class="field-group">
                        <label for="contact">Contact Number</label>
                        <input id="contact" type="text" name="contact" value="{{ old('contact') }}" required placeholder="0917xxxxxxx" class="form-control" />
                    </div>
                </div>
            </div>

            <div class="section-card">
                <div class="form-section-header">
                    <div class="section-icon"><i class="bi bi-lock-fill"></i></div>
                    <h3>Account Security</h3>
                </div>
                <div class="field-row">
                    <div class="field-group">
                        <label for="password">Password</label>
                        <div class="password-wrapper">
                            <input id="password" type="password" name="password" required minlength="6" autocomplete="new-password" class="form-control" placeholder="Enter a secure password" />
                            <button type="button" class="button button-secondary" data-password-toggle style="position:absolute; right:10px; top:50%; transform:translateY(-50%); padding:8px 12px; min-height:auto; min-width:auto; border-radius:999px;">Show</button>
                        </div>
                    </div>
                    <div class="field-group">
                        <label for="password_confirmation">Confirm Password</label>
                        <div class="password-wrapper">
                            <input id="password_confirmation" type="password" name="password_confirmation" required minlength="6" autocomplete="new-password" class="form-control" placeholder="Re-enter password" />
                            <button type="button" class="button button-secondary" data-password-toggle style="position:absolute; right:10px; top:50%; transform:translateY(-50%); padding:8px 12px; min-height:auto; min-width:auto; border-radius:999px;">Show</button>
                        </div>
                    </div>
                </div>
                <div class="field-row">
                    <div class="field-group">
                        <label class="checkbox-label" for="remember">
                            <input id="remember" type="checkbox" name="remember" class="form-checkbox" />
                            Remember me on this device
                        </label>
                    </div>
                </div>

                <div class="hero-actions public-login-actions" style="margin-top: 16px;">
                    <button type="submit" class="button button-primary button-large">Create Account</button>
                    <a href="/login" class="button button-secondary button-large">Back to Login</a>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
            button.addEventListener('click', function () {
                var input = this.previousElementSibling;
                if (!input) {
                    return;
                }

                if (input.type === 'password') {
                    input.type = 'text';
                    this.textContent = 'Hide';
                } else {
                    input.type = 'password';
                    this.textContent = 'Show';
                }
            });
        });
    });
</script>
@endsection

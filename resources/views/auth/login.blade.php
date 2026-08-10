@extends('layouts.app')

@section('title', 'Login | UM Attendance')

@section('content')
<div class="public-form-panel">
    <div class="public-form-box">
        <div class="landing-pill">UM</div>
        <div class="public-form-heading">
            <p class="landing-badge">UM TAEKWONDO &amp; KARATEDO</p>
            <h1 class="page-title" style="font-size: clamp(2.6rem, 4vw, 3.4rem);">Welcome Back</h1>
            <p class="landing-subtitle">Sign in to access your attendance portal, schedules, announcements, and athlete tools.</p>
        </div>

        <form method="POST" action="/login" class="form-panel public-login-form">
            @csrf
            <div class="field-group">
                <label for="identifier">Email or Student ID</label>
                <input id="identifier" type="text" name="identifier" value="{{ old('identifier') }}" placeholder="Enter your Student ID or Email" required class="form-control" />
            </div>
            <div class="field-group password-wrapper" style="position:relative;">
                <label for="password">Password</label>
                <input id="password" type="password" name="password" placeholder="Enter your password" required class="form-control" />
                <button type="button" class="button password-toggle" aria-label="Show password"><i class="bi bi-eye"></i></button>
            </div>
            <div class="hero-actions public-login-actions">
                <button type="submit" class="button button-primary button-large">Login</button>
                <a href="/register" class="button button-secondary button-large">Register Student Account</a>
            </div>
        </form>
    </div>
</div>
@endsection

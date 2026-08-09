@extends('layouts.app')

@section('title', 'Create Coach | UM Attendance')

@section('content')
<div class="page-panel">
    <div class="large-box">
        <h2 class="page-title">Create Coach Account</h2>
        <p class="hero-copy">Use this form to add a new coach for either Taekwondo or Karatedo.</p>
        <form method="POST" action="/admin/create-coach" class="form-panel" enctype="multipart/form-data">
            @csrf
            <div class="field-row">
                <div class="field-group">
                    <label for="first_name">First Name</label>
                    <input id="first_name" name="first_name" type="text" required class="form-control" />
                </div>
                <div class="field-group">
                    <label for="middle_name">Middle Initial (optional)</label>
                    <input id="middle_name" name="middle_name" type="text" class="form-control" />
                </div>
            </div>

            <div class="field-row">
                <div class="field-group">
                    <label for="last_name">Last Name</label>
                    <input id="last_name" name="last_name" type="text" required class="form-control" />
                </div>
                <div class="field-group">
                    <label for="contact">Contact Number</label>
                    <input id="contact" name="contact" type="text" placeholder="0917xxxxxxx" class="form-control" />
                </div>
            </div>

 <div class="field-row">
                <div class="field-group">
                    <label for="sport">Sport</label>
                    <select id="sport" name="sport" required class="form-control">
                        <option value="">Choose sport</option>
                        <option value="taekwondo">Taekwondo</option>
                        <option value="karatedo">Karatedo</option>
                    </select>
                </div>
                <div class="field-group"></div>
            </div>


            <div class="field-row">
                <div class="field-group">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" required class="form-control" />
                </div>
                <div class="field-group">
                    <label for="password">Password</label>
                    <div class="password-wrapper">
                        <input id="password" name="password" type="password" required class="form-control" />
                        <button type="button" class="button password-toggle" aria-label="Show password"><i class="bi bi-eye"></i></button>
                    </div>
                </div>
            </div>

            <div class="field-row">
                <div class="field-group">
                    <label for="password_confirmation">Confirm Password</label>
                    <div class="password-wrapper">
                        <input id="password_confirmation" name="password_confirmation" type="password" required class="form-control" />
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

           
            <button type="submit" class="button button-primary">Create Coach</button>
        </form>
    </div>
</div>
@endsection

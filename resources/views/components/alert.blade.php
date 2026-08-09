@php
    $hasSuccess = session('success') !== null;
    $hasError = session('error') !== null;
    $hasErrors = $errors->any();
@endphp

@if ($hasSuccess || $hasError || $hasErrors)
    <div class="toast-stack" aria-live="polite" aria-atomic="true">
        @if ($hasSuccess)
            <div class="toast toast-success" role="status">
                <div class="toast-icon"><i class="bi bi-check-circle"></i></div>
                <div class="toast-content">
                    <strong>Success</strong>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if ($hasError)
            <div class="toast toast-error" role="alert">
                <div class="toast-icon"><i class="bi bi-exclamation-circle"></i></div>
                <div class="toast-content">
                    <strong>Notice</strong>
                    <span>{{ session('error') }}</span>
                </div>
            </div>
        @endif

        @if ($hasErrors)
            <div class="toast toast-error" role="alert">
                <div class="toast-icon"><i class="bi bi-exclamation-triangle"></i></div>
                <div class="toast-content">
                    <strong>Review the form below</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif
    </div>
@endif

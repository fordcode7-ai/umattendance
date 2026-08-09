@props(['type' => 'info', 'message' => ''])
<div class="toast toast-{{ $type }}" role="status" aria-live="polite">
    <div class="toast-icon"><i class="bi bi-check-circle"></i></div>
    <div class="toast-content">{{ $message }}</div>
</div>

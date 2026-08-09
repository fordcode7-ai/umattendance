@props(['status' => 'info'])
@php
    $classes = [
        'present' => 'status-badge-present',
        'late' => 'status-badge-late',
        'absent' => 'status-badge-absent',
        'excuse' => 'status-badge-excuse',
        'special_training' => 'status-badge-special-training',
        'no_training' => 'status-badge-no-training',
        'info' => 'status-badge-info',
    ];
@endphp
<span class="status-badge {{ $classes[$status] ?? $classes['info'] }}">{{ $slot }}</span>

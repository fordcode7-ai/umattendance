@props(['student'])
@php
    $severity = $student['color'] ?? 'green';
    $border = $severity === 'red' ? ' border-urgent' : ($severity === 'yellow' ? ' border-warning' : ' border-success');
@endphp
<div class="athlete-card{{ $border }}">
    <div class="athlete-card-header">
        <div class="athlete-avatar">{{ strtoupper(substr($student['first_name'],0,1)) }}{{ strtoupper(substr($student['last_name'],0,1)) }}</div>
        <div>
            <div class="athlete-name">{{ $student['first_name'] }} {{ $student['last_name'] }}</div>
            <div class="athlete-meta">{{ $student['student_id'] }} · {{ ucfirst($student['sport']) }}</div>
        </div>
    </div>
    <div class="athlete-stats">
        <div class="athlete-stat"><strong>{{ $student['counts']['present'] }}</strong><span>Present</span></div>
        <div class="athlete-stat"><strong>{{ $student['counts']['late'] }}</strong><span>Late</span></div>
        <div class="athlete-stat"><strong>{{ $student['counts']['absent'] }}</strong><span>Absent</span></div>
        <div class="athlete-stat"><strong>{{ $student['counts']['excuse'] }}</strong><span>Excused</span></div>
    </div>
    <div class="athlete-status {{ $severity }}">{{ ucfirst($severity) }}</div>
</div>

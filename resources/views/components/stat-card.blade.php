@props(['icon' => 'bi-speedometer2', 'label' => '', 'value' => '', 'suffix' => '', 'theme' => ''] )
<div class="stat-card {{ $theme ? 'stat-card-'.$theme : '' }}">
    <div class="stat-card-icon"><i class="bi {{ $icon }}"></i></div>
    <div>
        <div class="stat-card-value">{{ $value }}{{ $suffix }}</div>
        <div class="stat-card-label">{{ $label }}</div>
    </div>
</div>

@props(['title' => '', 'value' => '', 'label' => '', 'icon' => null, 'color' => ''])

<div class="dashboard-metric-card" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); border-radius: 22px; padding: 16px; min-width: 160px; width: 100%; box-sizing: border-box;">
    <div style="display:flex; align-items:center; justify-content:space-between; gap:10px; margin-bottom: 10px;">
        <div style="display:flex; align-items:center; gap:10px;">
            <span style="width: 12px; height: 12px; border-radius: 999px; background: {{ $color ?? '#f4c542' }};"></span>
            <span style="color: rgba(247,245,242,0.75); font-size: 0.92rem; text-transform: uppercase; letter-spacing: 0.08em;">{{ $title }}</span>
        </div>
        @if($icon)
            <span style="color: {{ $color ?? '#f4c542' }}; font-size: 1.25rem;">{!! $icon !!}</span>
        @endif
    </div>
    <div style="display:flex; align-items:center; justify-content:space-between; gap:12px;">
        <strong style="font-size: 2rem; display:block; color: var(--um-white);">{{ $value }}</strong>
        <span style="color: rgba(247,245,242,0.68); font-size: 0.88rem;">{{ $label }}</span>
    </div>
</div>

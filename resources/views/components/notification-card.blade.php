@props([
    'title' => '',
    'description' => '',
    'actionUrl' => '#',
    'actionLabel' => 'Acknowledge',
    'items' => [],
])

<div class="large-box notification-card">
    <div class="notification-card-header">
        <div>
            <span class="section-accent">Notification</span>
            <h2 class="notification-card-title">{{ $title }}</h2>
            <p class="hero-copy notification-card-description">{{ $description }}</p>
        </div>
        <form method="POST" action="{{ $actionUrl }}" class="notification-card-action">
            @csrf
            <button type="submit" class="button button-primary button-small">{{ $actionLabel }}</button>
        </form>
    </div>

    @if(!empty($items))
        <ul class="notification-list notification-card-items">
            @foreach($items as $item)
                <li class="notification-card-item">
                    <span>{!! $item['label'] !!}</span>
                    @if(!empty($item['new']))
                        <span class="notification-pill">New</span>
                    @endif
                </li>
            @endforeach
        </ul>
    @endif
</div>

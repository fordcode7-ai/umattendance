@php
    $user = session('user') ?? [];
    $currentPath = trim(request()->path(), '/');
    $navItems = [];

    if (($user['role'] ?? null) === 'student') {
        $navItems = [
            ['label' => 'Dashboard', 'href' => '/student/dashboard', 'icon' => 'bi-speedometer2'],
            ['label' => 'Team Status', 'href' => '/student/roster', 'icon' => 'bi-people-fill'],
            ['label' => 'Schedule', 'href' => '/student/schedule', 'icon' => 'bi-calendar-event'],
            ['label' => 'Announcements', 'href' => '/student/announcements', 'icon' => 'bi-chat-square-text'],
            ['label' => 'Excuse Requests', 'href' => '/student/excuse', 'icon' => 'bi-file-earmark-text'],
            ['label' => 'Special Training', 'href' => '/student/special-training', 'icon' => 'bi-stars'],
            ['label' => 'Logout', 'href' => '/logout', 'icon' => 'bi-box-arrow-right'],
        ];
    } elseif (($user['role'] ?? null) === 'coach') {
        $navItems = [
            ['label' => 'Dashboard', 'href' => '/coach/dashboard', 'icon' => 'bi-speedometer2'],
        ];

        $coachSport = $user['sport'] ?? null;
        if ($coachSport === 'taekwondo' || $coachSport === 'all') {
            $navItems[] = ['label' => 'Taekwondo Schedule', 'href' => '/coach/schedule/taekwondo', 'icon' => 'bi-calendar-event'];
        }
        if ($coachSport === 'karatedo' || $coachSport === 'all') {
            $navItems[] = ['label' => 'Karatedo Schedule', 'href' => '/coach/schedule/karatedo', 'icon' => 'bi-calendar-event'];
        }

        $navItems = array_merge($navItems, [
            ['label' => 'Excuse Requests', 'href' => '/coach/excuses', 'icon' => 'bi-file-earmark-text'],
            ['label' => 'Special Training', 'href' => '/coach/special-training', 'icon' => 'bi-stars'],
            ['label' => 'Announcements', 'href' => '/coach/announcements', 'icon' => 'bi-chat-square-text'],
            ['label' => 'Logout', 'href' => '/logout', 'icon' => 'bi-box-arrow-right'],
        ]);
    } elseif (($user['role'] ?? null) === 'admin') {
        $navItems = [
            ['label' => 'Dashboard', 'href' => '/admin/dashboard', 'icon' => 'bi-speedometer2'],
            ['label' => 'Coaches', 'href' => '/admin/coaches', 'icon' => 'bi-person-badge'],
            ['label' => 'Excuse Requests', 'href' => '/admin/excuses', 'icon' => 'bi-file-earmark-text'],
            ['label' => 'Special Training', 'href' => '/admin/special-training', 'icon' => 'bi-stars'],
            ['label' => 'System Settings', 'href' => '/admin/system-settings', 'icon' => 'bi-gear'],
            ['label' => 'Logout', 'href' => '/logout', 'icon' => 'bi-box-arrow-right'],
        ];
    }
@endphp

<aside class="sidebar" data-sidebar>
    <div class="sidebar-header">
        <div class="brand-mark">UM</div>
        <div class="brand-copy">
            <strong>University of Mindanao</strong>
            <span>Taekwondo &amp; Karatedo</span>
        </div>
    </div>

    <nav class="nav-group" aria-label="Main navigation">
        @foreach($navItems as $item)
            @php
                $active = $currentPath === trim(ltrim($item['href'], '/'), '/') || str_starts_with($currentPath, trim(ltrim($item['href'], '/'), '/'));
            @endphp
            <a href="{{ $item['href'] }}" class="nav-item {{ $active ? 'active' : '' }}">
                <div class="nav-icon"><i class="bi {{ $item['icon'] }}"></i></div>
                <span class="nav-label">{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>

    <div class="nav-fill"></div>

    <div class="sidebar-footer">
        <strong>Sports Portal</strong>
        Discipline and performance, organized.
    </div>
</aside>

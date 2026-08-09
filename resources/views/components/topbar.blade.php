@php
    use App\Services\AttendanceStore;

    $user = session('user');
    $pageHeading = trim($__env->yieldContent('pageHeading') ?: str_replace(' | UM Attendance', '', $__env->yieldContent('title')));
    $avatarLetters = strtoupper(substr($user['first_name'] ?? 'U', 0, 1).substr($user['last_name'] ?? '', 0, 1));
    $avatarUrl = $user['avatar'] ?? null;
    $notificationCount = 0;

    if ($user) {
        if (($user['role'] ?? null) === 'student') {
            $notificationCount = AttendanceStore::getUnreadNotificationCount($user['student_id'], $user['sport']);
        } elseif (($user['role'] ?? null) === 'coach') {
            $notificationCount = AttendanceStore::getPendingExcuseCount($user['sport']) + AttendanceStore::getPendingSpecialTrainingRequestCount($user['sport']);
        } elseif (($user['role'] ?? null) === 'admin') {
            $notificationCount = AttendanceStore::getPendingExcuseCount(null);
        }
    }
@endphp

<header class="topbar">
    <div class="topbar-left">
        <h1 class="topbar-title">{{ $pageHeading ?: 'Dashboard' }}</h1>
        <div class="topbar-meta">
            <span>{{ now()->format('F j, Y') }}</span>
            <span>{{ isset($user['role']) ? ucfirst($user['role']) : 'Member' }}</span>
        </div>
    </div>

    <div class="topbar-actions">
        <button class="mobile-nav-toggle" type="button" data-mobile-toggle aria-label="Open sidebar"><i class="bi bi-list"></i></button>
        @php
            $notificationUrl = '/student/announcements';
            if (($user['role'] ?? null) === 'student') {
                $studentId = $user['student_id'] ?? null;
                $sport = $user['sport'] ?? null;
                if ($studentId && $sport && AttendanceStore::hasUnreadScheduleNotifications($studentId, $sport)) {
                    $notificationUrl = '/student/schedule';
                } elseif ($studentId && count(AttendanceStore::getUnacknowledgedApprovedSpecialTraining($studentId)) > 0) {
                    $notificationUrl = '/student/special-training';
                } elseif ($studentId && count(AttendanceStore::getUnacknowledgedApprovedExcuses($studentId)) > 0) {
                    $notificationUrl = '/student/excuse';
                } else {
                    $notificationUrl = '/student/announcements';
                }
            } elseif (($user['role'] ?? null) === 'coach') {
                $sport = $user['sport'] ?? null;
                if ($sport && AttendanceStore::getPendingSpecialTrainingRequestCount($sport) > 0) {
                    $notificationUrl = '/coach/special-training';
                } else {
                    $notificationUrl = '/coach/excuses';
                }
            } elseif (($user['role'] ?? null) === 'admin') {
                $notificationUrl = '/admin/excuses';
            }
        @endphp

        <a href="{{ $notificationUrl }}" class="icon-btn notification-btn" aria-label="Notifications">
            <i class="bi bi-bell"></i>
            @if($notificationCount > 0)
                <span class="icon-badge">{{ $notificationCount }}</span>
            @endif
        </a>
        <a href="{{ route('profile.show') }}" class="profile-chip" aria-label="Open profile">
            @if($avatarUrl)
                <img src="{{ $avatarUrl }}" alt="Profile photo" class="avatar avatar-image" />
            @else
                <span class="avatar">{{ $avatarLetters }}</span>
            @endif
            <div>
                <div>{{ trim(($user['first_name'] ?? 'UM').' '.($user['last_name'] ?? '')) }}</div>
                <small>{{ isset($user['role']) ? ucfirst($user['role']) : 'Member' }}</small>
            </div>
        </a>
    </div>
</header>

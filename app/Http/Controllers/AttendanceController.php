<?php

namespace App\Http\Controllers;

use App\Services\AttendanceStore;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function welcome()
    {
        return view('landing');
    }

    public function showLogin()
    {
        return redirect('/#login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string|max:255',
            'password' => 'required|string',
        ]);

        $identifier = trim($request->input('identifier'));
        $user = null;

        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            $user = AttendanceStore::findUserByEmail($identifier);
        } else {
            $user = AttendanceStore::findUserByStudentId($identifier);
        }

        if ($user && $user['password'] === $request->input('password')) {
            session(['user' => $user]);
            if ($user['role'] === 'student') {
                return redirect('/student/dashboard');
            }
            if ($user['role'] === 'coach') {
                return redirect('/coach/dashboard');
            }
            if ($user['role'] === 'admin') {
                return redirect('/admin/dashboard');
            }
        }

        return back()->withErrors(['identifier' => 'Login credentials are incorrect.'])->withInput();
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'student_id' => 'required|string|max:50',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'year_level' => 'required|string|max:10',
            'course' => 'required|string|max:120',
            'contact' => 'required|string|max:30',
            'sport' => 'required|in:taekwondo,karatedo',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $studentId = trim($request->input('student_id'));
        if (AttendanceStore::findUserByStudentId($studentId)) {
            return back()->withErrors(['student_id' => 'This Student ID has already been registered.'])->withInput();
        }

        $user = [
            'id' => uniqid('student_'),
            'student_id' => $studentId,
            'email' => null,
            'password' => $request->input('password'),
            'role' => 'student',
            'first_name' => $request->input('first_name'),
            'middle_name' => $request->input('middle_name') ?: '',
            'last_name' => $request->input('last_name'),
            'year_level' => $request->input('year_level'),
            'course' => $request->input('course'),
            'contact' => $request->input('contact'),
            'sport' => $request->input('sport'),
            'avatar' => null,
        ];

        AttendanceStore::addUser($user);
        session(['user' => $user]);
        return redirect('/student/dashboard')->with('success', 'Student account created successfully with your university-issued ID.');
    }

    public function logout()
    {
        session()->flush();
        return redirect('/');
    }

    public function showProfile()
    {
        $user = AttendanceStore::currentUser();
        if (!$user) {
            return redirect('/login');
        }

        return view('student.profile', [
            'user' => $user,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = AttendanceStore::currentUser();
        if (!$user) {
            return redirect('/login');
        }

        $rules = [
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'contact' => 'required|string|max:30',
            'avatar' => 'nullable|image|max:4096',
        ];

        if ($user['role'] === 'coach') {
            $rules['email'] = 'required|email|max:255';
            $rules['title'] = 'nullable|string|max:120';
            $rules['sports_handled'] = 'nullable|array';
            $rules['sports_handled.*'] = 'in:taekwondo,karatedo';
        } else {
            $rules['email'] = 'nullable|email|max:255';
            $rules['year_level'] = 'required|string|max:10';
            $rules['course'] = 'required|string|max:120';
        }

        $request->validate($rules);

        $updates = [
            'first_name' => $request->input('first_name'),
            'middle_name' => $request->input('middle_name') ?: '',
            'last_name' => $request->input('last_name'),
            'contact' => $request->input('contact'),
        ];

        if ($user['role'] === 'coach') {
            $updates['email'] = $request->input('email');
            $updates['title'] = $request->input('title') ?: '';
            $sportsHandled = $request->input('sports_handled', []);
            $updates['sports_handled'] = is_array($sportsHandled) ? array_values($sportsHandled) : [];
        } else {
            $updates['year_level'] = $request->input('year_level');
            $updates['course'] = $request->input('course');
        }

        if ($user['role'] === 'coach') {
            $updates['title'] = $request->input('title') ?: '';
            $sportsHandled = $request->input('sports_handled', []);
            $updates['sports_handled'] = is_array($sportsHandled) ? array_values($sportsHandled) : [];
        } else {
            $request->validate([
                'year_level' => 'required|string|max:10',
                'course' => 'required|string|max:120',
            ]);
            $updates['year_level'] = $request->input('year_level');
            $updates['course'] = $request->input('course');
        }

        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $updates['avatar'] = '/storage/' . $avatarPath;
        }

        $savedUser = AttendanceStore::updateUser($user['id'], $updates);
        if ($savedUser) {
            session(['user' => $savedUser]);
        }

        return back()->with('success', 'Your profile has been updated.');
    }

    protected function authorizeStudent()
    {
        $user = AttendanceStore::currentUser();
        if (!$user || $user['role'] !== 'student') {
            return redirect('/login');
        }

        if (!empty($user['student_id']) && !empty($user['sport'])) {
            AttendanceStore::autoMarkAbsentIfNeeded($user['student_id'], $user['sport']);
        }

        return $user;
    }

    public function studentDashboard(Request $request)
    {
        $user = $this->authorizeStudent();
        if (!$user) {
            return redirect('/login');
        }

        $year = $request->query('year', now()->year);
        $month = $request->query('month', now()->month);
        $year = (int) $year;
        $month = (int) $month;

        $calendar = AttendanceStore::getMonthlyStatusCalendar($user['student_id'], $year, $month);
        $counts = AttendanceStore::getMonthCounts($user['student_id'], $year, $month);
        $today = now()->format('Y-m-d');
        $todayAttendance = AttendanceStore::getAttendanceForDate($user['student_id'], $today);
        $startDate = AttendanceStore::systemStartDate();
        $isBeforeStartDate = strtotime($today) < strtotime($startDate);
        $isTodaySunday = date('w', strtotime($today)) === 0;
        $monthKey = sprintf('%d-%02d', $year, $month);
        $latestSchedule = AttendanceStore::getLatestSchedule($user['sport'], $monthKey);
        $latestAnnouncement = AttendanceStore::getLatestAnnouncement($user['sport'], $monthKey);
        $unreadSchedule = AttendanceStore::hasUnreadScheduleNotifications($user['student_id'], $user['sport']);

        return view('student.dashboard', [
            'user' => $user,
            'calendar' => $calendar,
            'counts' => $counts,
            'year' => $year,
            'month' => $month,
            'monthName' => date('F', strtotime("{$year}-{$month}-01")),
            'latestSchedule' => $latestSchedule,
            'latestAnnouncement' => $latestAnnouncement,
            'unreadSchedule' => $unreadSchedule,
            'todayAttendance' => $todayAttendance,
            'isTodaySunday' => $isTodaySunday,
            'isBeforeStartDate' => $isBeforeStartDate,
            'attendanceStartDate' => $startDate,
        ]);
    }

    public function studentAttend(Request $request)
    {
        $user = $this->authorizeStudent();
        if (!$user) {
            return redirect('/login');
        }

        $request->validate([
            'action' => 'required|in:present,late,absent',
        ]);

        $date = now()->format('Y-m-d');
        $startDate = AttendanceStore::systemStartDate();
        if (strtotime($date) < strtotime($startDate)) {
            return back()->with('error', "Attendance tracking starts on {$startDate}. Please submit on or after that date.");
        }

        if (now()->isSunday()) {
            return back()->with('error', 'Sunday is a no-training day. Attendance cannot be submitted.');
        }

        $action = $request->input('action');
        $time = now()->format('H:i');
        $sport = $user['sport'];
        $existingRecord = AttendanceStore::getAttendanceForDate($user['student_id'], $date);

        if (!empty($existingRecord) && $existingRecord['status'] === 'no_training') {
            return back()->with('error', 'Attendance cannot be submitted on a no-training day.');
        }

        if ($action === 'present') {
            $cutoff = now()->copy()->setTime(8, 30);
            $status = now()->lessThanOrEqualTo($cutoff) ? 'present' : 'late';
        } else {
            $status = $action;
        }

        AttendanceStore::addAttendance($user['student_id'], $date, $status, $time, $sport, 'Checked in');

        return redirect('/student/dashboard')->with('success', "Attendance saved as {$status} at {$time}.");
    }

    public function showExcuse()
    {
        $user = $this->authorizeStudent();
        if (!$user) {
            return redirect('/login');
        }

        $unacknowledgedExcuses = AttendanceStore::getUnacknowledgedApprovedExcuses($user['student_id']);

        return view('student.excuse', [
            'user' => $user,
            'unacknowledgedExcuses' => $unacknowledgedExcuses,
        ]);
    }

    public function sendExcuse(Request $request)
    {
        $user = $this->authorizeStudent();
        if (!$user) {
            return redirect('/login');
        }

        $request->validate([
            'date' => 'required|date',
            'title' => 'required|string|max:120',
            'reason' => 'required|string|max:400',
            'photos.*' => 'nullable|image|max:4096',
        ]);

        AttendanceStore::addExcuse(
            $user['student_id'],
            $request->input('date'),
            $request->input('title'),
            $request->input('reason'),
            $request->file('photos', []),
            $user['sport']
        );

        return redirect('/student/dashboard')->with('success', 'Your excuse request was sent to the coach for review.');
    }

    public function showSpecialTraining()
    {
        $user = $this->authorizeStudent();
        if (!$user) {
            return redirect('/login');
        }

        return view('student.special_training', [
            'user' => $user,
        ]);
    }

    public function sendSpecialTraining(Request $request)
    {
        $user = $this->authorizeStudent();
        if (!$user) {
            return redirect('/login');
        }

        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'title' => 'required|string|max:120',
            'reason' => 'required|string|max:400',
            'photos.*' => 'nullable|image|max:4096',
        ]);

        AttendanceStore::addSpecialTrainingRequest(
            $user['student_id'],
            $request->input('start_date'),
            $request->input('end_date'),
            $request->input('title'),
            $request->input('reason'),
            $request->file('photos', []),
            $user['sport']
        );

        return redirect('/student/special-training')->with('success', 'Your special training request was sent to your coach for approval.');
    }

    public function studentRoster(Request $request)
    {
        $user = $this->authorizeStudent();
        if (!$user) {
            return redirect('/login');
        }

        $year = (int) $request->query('year', now()->year);
        $month = (int) $request->query('month', now()->month);
        $currentMonth = \DateTime::createFromFormat('!Y-m', sprintf('%04d-%02d', $year, $month));
        if (!$currentMonth) {
            $currentMonth = new \DateTime();
            $year = (int) $currentMonth->format('Y');
            $month = (int) $currentMonth->format('m');
        }

        $students = AttendanceStore::allStudentsStatus($user['sport'], $year, $month);
        $search = $request->query('search', '');
        if ($search) {
            $students = array_filter($students, function ($student) use ($search) {
                return stripos($student['first_name'] . ' ' . $student['last_name'], $search) !== false;
            });
        }

        $prevMonth = (clone $currentMonth)->modify('-1 month');
        $nextMonth = (clone $currentMonth)->modify('+1 month');

        return view('student.roster', [
            'user' => $user,
            'students' => $students,
            'search' => $search,
            'year' => $year,
            'month' => $month,
            'currentMonthLabel' => $currentMonth->format('F Y'),
            'prevMonthYear' => (int) $prevMonth->format('Y'),
            'prevMonthValue' => (int) $prevMonth->format('m'),
            'nextMonthYear' => (int) $nextMonth->format('Y'),
            'nextMonthValue' => (int) $nextMonth->format('m'),
        ]);
    }

    public function studentSchedule(Request $request)
    {
        $user = $this->authorizeStudent();
        if (!$user) {
            return redirect('/login');
        }

        $monthKey = $request->query('month', now()->format('Y-m'));
        $selectedMonth = \DateTime::createFromFormat('!Y-m', $monthKey);
        if (!$selectedMonth) {
            $selectedMonth = new \DateTime();
            $monthKey = $selectedMonth->format('Y-m');
        }

        $schedules = AttendanceStore::getSchedules($user['sport'], $monthKey);
        $unreadSchedule = AttendanceStore::hasUnreadScheduleNotifications($user['student_id'], $user['sport']);
        $schedulePublishedAt = AttendanceStore::getScheduleUpdateTime($user['sport']);

        $availableMonths = [];
        $monthAnchor = (clone $selectedMonth)->modify('-1 month');
        for ($i = 0; $i < 12; $i++) {
            $availableMonths[$monthAnchor->format('Y-m')] = $monthAnchor->format('F Y');
            $monthAnchor = $monthAnchor->modify('+1 month');
        }

        $prevMonth = (clone $selectedMonth)->modify('-1 month')->format('Y-m');
        $nextMonth = (clone $selectedMonth)->modify('+1 month')->format('Y-m');

        return view('student.schedule', [
            'user' => $user,
            'schedules' => $schedules,
            'unreadSchedule' => $unreadSchedule,
            'schedulePublishedAt' => $schedulePublishedAt,
            'monthKey' => $monthKey,
            'monthLabel' => $selectedMonth->format('F Y'),
            'availableMonths' => $availableMonths,
            'prevMonth' => $prevMonth,
            'nextMonth' => $nextMonth,
        ]);
    }

    public function studentAnnouncements(Request $request)
    {
        $user = $this->authorizeStudent();
        if (!$user) {
            return redirect('/login');
        }

        $monthKey = now()->format('Y-m');
        $announcements = AttendanceStore::getAnnouncements($user['sport'], $monthKey);
        $unreadAnnouncements = AttendanceStore::hasUnreadAnnouncementNotifications($user['student_id'], $user['sport']);

        return view('student.announcements', [
            'user' => $user,
            'announcements' => $announcements,
            'unreadAnnouncements' => $unreadAnnouncements,
        ]);
    }

    public function acknowledgeNotification(Request $request, string $type)
    {
        $user = $this->authorizeStudent();
        if (!$user) {
            return redirect('/login');
        }

        if (!in_array($type, ['schedule', 'announcement', 'excuse', 'special_training'], true)) {
            return back();
        }

        AttendanceStore::acknowledgeStudentNotification($user['student_id'], $type);
        return back();
    }
}

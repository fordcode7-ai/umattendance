<?php

namespace App\Http\Controllers;

use App\Services\AttendanceStore;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    protected function authorizeAdmin()
    {
        $user = AttendanceStore::currentUser();
        if (!$user || $user['role'] !== 'admin') {
            return null;
        }
        return $user;
    }

    public function dashboard(Request $request)
    {
        $user = $this->authorizeAdmin();
        if (!$user) {
            return redirect('/login');
        }

        $year = (int) $request->query('year', now()->year);
        $month = (int) $request->query('month', now()->month);
        $taekwondoStudents = AttendanceStore::allStudentsStatus('taekwondo', $year, $month);
        $karatedoStudents = AttendanceStore::allStudentsStatus('karatedo', $year, $month);
        $search = $request->query('search', '');
        if ($search) {
            $taekwondoStudents = array_filter($taekwondoStudents, function ($student) use ($search) {
                return stripos($student['first_name'] . ' ' . $student['last_name'], $search) !== false;
            });
            $karatedoStudents = array_filter($karatedoStudents, function ($student) use ($search) {
                return stripos($student['first_name'] . ' ' . $student['last_name'], $search) !== false;
            });
        }

        return view('admin.dashboard', [
            'user' => $user,
            'taekwondoStudents' => $taekwondoStudents,
            'karatedoStudents' => $karatedoStudents,
            'year' => $year,
            'month' => $month,
            'search' => $search,
        ]);
    }

    public function showCreateCoach()
    {
        $user = $this->authorizeAdmin();
        if (!$user) {
            return redirect('/login');
        }

        return view('admin.create_coach', ['user' => $user]);
    }

    public function coaches()
    {
        $user = $this->authorizeAdmin();
        if (!$user) {
            return redirect('/login');
        }

        $coaches = AttendanceStore::allCoaches();
        return view('admin.coaches', [
            'user' => $user,
            'coaches' => $coaches,
        ]);
    }

    public function editCoach(string $id)
    {
        $user = $this->authorizeAdmin();
        if (!$user) {
            return redirect('/login');
        }

        $coach = AttendanceStore::findUserById($id);
        if (!$coach || ($coach['role'] ?? null) !== 'coach') {
            return redirect('/admin/coaches');
        }

        return view('admin.edit_coach', ['user' => $user, 'coach' => $coach]);
    }

    public function updateCoach(Request $request, string $id)
    {
        $user = $this->authorizeAdmin();
        if (!$user) {
            return redirect('/login');
        }

        $request->validate([
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:10',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email',
            'sport' => 'required|in:taekwondo,karatedo,all',
            'contact' => 'nullable|string|max:40',
            'password' => 'nullable|string|min:6',
        ]);

        $updates = [
            'first_name' => $request->input('first_name'),
            'middle_name' => $request->input('middle_name') ?: '',
            'last_name' => $request->input('last_name'),
            'email' => $request->input('email'),
            'sport' => $request->input('sport'),
            'contact' => $request->input('contact') ?: null,
        ];
        if ($request->filled('password')) {
            $updates['password'] = $request->input('password');
        }

        if ($request->hasFile('avatar') && $request->file('avatar')->isValid()) {
            $path = $request->file('avatar')->store('avatars', 'public');
            AttendanceStore::setUserAvatar($id, '/storage/' . ltrim($path, '/'));
        }

        AttendanceStore::updateUser($id, $updates);
        return redirect('/admin/coaches')->with('success', 'Coach updated successfully.');
    }

    public function deleteCoach(string $id)
    {
        $user = $this->authorizeAdmin();
        if (!$user) {
            return redirect('/login');
        }

        AttendanceStore::deleteUserById($id);
        return redirect('/admin/coaches')->with('success', 'Coach account deleted.');
    }

    public function createCoach(Request $request)
    {
        $user = $this->authorizeAdmin();
        if (!$user) {
            return redirect('/login');
        }

        $request->validate([
            'email' => 'required|email',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'sport' => 'required|in:taekwondo,karatedo',
            'password' => 'required|string|min:6',
            'avatar' => 'nullable|image|max:4096',
        ]);

        $coach = [
            'id' => uniqid('coach_'),
            'student_id' => null,
            'email' => $request->input('email'),
            'password' => $request->input('password'),
            'role' => 'coach',
            'first_name' => $request->input('first_name'),
            'middle_name' => $request->input('middle_name') ?: '',
            'last_name' => $request->input('last_name'),
            'year_level' => null,
            'course' => null,
            'contact' => $request->input('contact') ?: null,
            'sport' => $request->input('sport'),
            'avatar' => null,
        ];

        if ($request->hasFile('avatar') && $request->file('avatar')->isValid()) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $coach['avatar'] = '/storage/' . ltrim($path, '/');
        }

        AttendanceStore::addUser($coach);
        return back()->with('success', 'Coach account created successfully.');
    }

    public function editStudent(string $studentId)
    {
        $user = $this->authorizeAdmin();
        if (!$user) {
            return redirect('/login');
        }

        $student = AttendanceStore::findUserByStudentId($studentId);
        if (!$student) {
            return redirect('/admin/dashboard');
        }

        return view('admin.edit_student', ['user' => $user, 'student' => $student]);
    }

    public function updateStudentAttendance(Request $request, string $studentId)
    {
        $user = $this->authorizeAdmin();
        if (!$user) {
            return redirect('/login');
        }

        $request->validate([
            'date' => 'required|date',
            'status' => 'required|in:present,late,absent,excuse',
            'time' => 'required|date_format:H:i',
        ]);

        AttendanceStore::updateAttendance($studentId, $request->input('date'), $request->input('status'), $request->input('time'));
        return back()->with('success', 'Student attendance updated.');
    }

    public function schedule(Request $request, string $sport)
    {
        $user = $this->authorizeAdmin();
        if (!$user) {
            return redirect('/login');
        }

        $monthKey = $request->query('month', now()->format('Y-m'));
        $selectedMonth = \DateTime::createFromFormat('!Y-m', $monthKey);
        if (!$selectedMonth) {
            $selectedMonth = new \DateTime();
            $monthKey = $selectedMonth->format('Y-m');
        }

        $schedules = AttendanceStore::getSchedules($sport, $monthKey);
        $schedulePublishedAt = AttendanceStore::getScheduleUpdateTime($sport);
        $pendingPublish = AttendanceStore::hasPendingScheduleChanges($sport);

        $availableMonths = [];
        $monthAnchor = (clone $selectedMonth)->modify('-1 month');
        for ($i = 0; $i < 12; $i++) {
            $availableMonths[$monthAnchor->format('Y-m')] = $monthAnchor->format('F Y');
            $monthAnchor = $monthAnchor->modify('+1 month');
        }

        $prevMonth = (clone $selectedMonth)->modify('-1 month')->format('Y-m');
        $nextMonth = (clone $selectedMonth)->modify('+1 month')->format('Y-m');

        return view('admin.schedule', [
            'user' => $user,
            'sport' => $sport,
            'schedules' => $schedules,
            'monthKey' => $monthKey,
            'monthLabel' => $selectedMonth->format('F Y'),
            'availableMonths' => $availableMonths,
            'prevMonth' => $prevMonth,
            'nextMonth' => $nextMonth,
            'schedulePublishedAt' => $schedulePublishedAt,
            'pendingPublish' => $pendingPublish,
        ]);
    }

    public function storeSchedule(Request $request, string $sport)
    {
        $user = $this->authorizeAdmin();
        if (!$user) {
            return redirect('/login');
        }

        $request->validate([
            'date' => 'required|date',
            'venue' => 'required|string|max:120',
            'time' => 'required|string|max:30',
        ]);

        // Prevent double-booking across sports
        $conflict = AttendanceStore::findScheduleConflict($sport, $request->input('date'), $request->input('time'), $request->input('venue'));
        if ($conflict) {
            return back()->withErrors(['date' => "Schedule conflict: {$conflict['sport']} already has a booking at {$request->input('venue')} on {$request->input('date')}."])->withInput();
        }

        AttendanceStore::setSchedule($sport, $request->input('date'), $request->input('time'), $request->input('venue'));
        return back()->with('success', 'Training schedule updated.');
    }

    public function updateSchedule(Request $request, string $sport, string $date)
    {
        $user = $this->authorizeAdmin();
        if (!$user) {
            return redirect('/login');
        }

        $request->validate([
            'date' => 'required|date',
            'venue' => 'required|string|max:120',
            'time' => 'required|string|max:30',
        ]);

        // Prevent double-booking when updating
        $conflict = AttendanceStore::findScheduleConflict($sport, $request->input('date'), $request->input('time'), $request->input('venue'));
        if ($conflict) {
            return back()->withErrors(['date' => "Schedule conflict: {$conflict['sport']} already has a booking at {$request->input('venue')} on {$request->input('date')}."])->withInput();
        }

        AttendanceStore::updateSchedule($sport, $date, $request->input('date'), $request->input('time'), $request->input('venue'));
        return back()->with('success', 'Schedule entry updated.');
    }

    public function deleteSchedule(Request $request, string $sport, string $date)
    {
        $user = $this->authorizeAdmin();
        if (!$user) {
            return redirect('/login');
        }

        AttendanceStore::deleteSchedule($sport, $date);
        return back()->with('success', 'Schedule entry deleted.');
    }

    public function excuses()
    {
        $user = $this->authorizeAdmin();
        if (!$user) {
            return redirect('/login');
        }

        $excuses = AttendanceStore::getExcuses('all');
        return view('admin.excuses', [
            'user' => $user,
            'excuses' => $excuses,
        ]);
    }

    public function showExcuseRequest(string $id)
    {
        $user = $this->authorizeAdmin();
        if (!$user) {
            return redirect('/login');
        }

        $excuse = AttendanceStore::findExcuseById($id);
        if (!$excuse) {
            return redirect('/admin/excuses')->withErrors(['excuse' => 'Excuse request not found.']);
        }

        $student = AttendanceStore::findUserByStudentId($excuse['student_id']);
        $excuse['student_display'] = $student
            ? trim($student['first_name'] . ' ' . $student['last_name']) . ' · ' . $excuse['student_id']
            : 'Student ID: ' . $excuse['student_id'];

        $excuse['submitted_at_label'] = !empty($excuse['submitted_at'])
            ? date('M j, Y', strtotime($excuse['submitted_at']))
            : null;

        return view('admin.excuse_detail', [
            'user' => $user,
            'excuse' => $excuse,
        ]);
    }

    public function specialTrainingList()
    {
        $user = $this->authorizeAdmin();
        if (!$user) {
            return redirect('/login');
        }

        $requests = AttendanceStore::getPendingSpecialTrainingRequests(null);
        $requests = array_map(function ($request) {
            $student = AttendanceStore::findUserByStudentId($request['student_id']);
            $request['student_display'] = $student
                ? trim($student['first_name'] . ' ' . $student['last_name']) . ' · ' . $request['student_id']
                : 'Student ID: ' . $request['student_id'];

            $start = strtotime($request['start_date']);
            $end = strtotime($request['end_date']);
            $request['date_range'] = date('M j, Y', $start);
            if ($request['start_date'] !== $request['end_date']) {
                $request['date_range'] .= ' – ' . date('M j, Y', $end);
            }

            $request['submitted_at_label'] = !empty($request['submitted_at'])
                ? date('M j, Y', strtotime($request['submitted_at']))
                : null;

            return $request;
        }, $requests);

        return view('admin.special_training', [
            'user' => $user,
            'requests' => $requests,
        ]);
    }

    public function showSpecialTrainingRequest(string $id)
    {
        $user = $this->authorizeAdmin();
        if (!$user) {
            return redirect('/login');
        }

        $request = AttendanceStore::getSpecialTrainingRequestById($id);
        if (!$request) {
            return redirect('/admin/special-training')->withErrors(['request' => 'Special training request not found.']);
        }

        $student = AttendanceStore::findUserByStudentId($request['student_id']);
        $request['student_display'] = $student
            ? trim($student['first_name'] . ' ' . $student['last_name']) . ' · ' . $request['student_id']
            : 'Student ID: ' . $request['student_id'];

        $start = strtotime($request['start_date']);
        $end = strtotime($request['end_date']);
        $request['date_range'] = date('M j, Y', $start);
        if ($request['start_date'] !== $request['end_date']) {
            $request['date_range'] .= ' – ' . date('M j, Y', $end);
        }

        $request['submitted_at_label'] = !empty($request['submitted_at'])
            ? date('M j, Y', strtotime($request['submitted_at']))
            : null;

        return view('admin.special_training_detail', [
            'user' => $user,
            'request' => $request,
        ]);
    }

    public function approveExcuse(string $excuseId)
    {
        $user = $this->authorizeAdmin();
        if (!$user) {
            return redirect('/login');
        }

        AttendanceStore::approveExcuse($excuseId);
        return back()->with('success', 'Excuse approved successfully.');
    }

    public function approveSpecialTraining(string $requestId)
    {
        $user = $this->authorizeAdmin();
        if (!$user) {
            return redirect('/login');
        }

        AttendanceStore::approveSpecialTraining($requestId);
        return back()->with('success', 'Special training request approved successfully.');
    }

    public function announcements()
    {
        $user = $this->authorizeAdmin();
        if (!$user) {
            return redirect('/login');
        }

        $announcements = AttendanceStore::getAnnouncements('all');
        return view('admin.announcements', [
            'user' => $user,
            'announcements' => $announcements,
        ]);
    }

    public function createAnnouncement(Request $request)
    {
        $user = $this->authorizeAdmin();
        if (!$user) {
            return redirect('/login');
        }

        $request->validate([
            'title' => 'required|string|max:120',
            'body' => 'required|string|max:500',
            'sport' => 'required|in:taekwondo,karatedo,all',
        ]);

        AttendanceStore::addAnnouncement($request->input('sport'), $request->input('title'), $request->input('body'));
        return back()->with('success', 'Announcement posted successfully.');
    }

    public function systemSettings(Request $request)
    {
        $user = $this->authorizeAdmin();
        if (!$user) {
            return redirect('/login');
        }

        $students = AttendanceStore::allStudents();
        usort($students, function ($a, $b) {
            $aLast = strtolower($a['last_name'] ?? '');
            $bLast = strtolower($b['last_name'] ?? '');
            if ($aLast !== $bLast) {
                return $aLast <=> $bLast;
            }
            return strtolower($a['first_name'] ?? '') <=> strtolower($b['first_name'] ?? '');
        });

        $search = $request->query('search', '');
        if ($search) {
            $students = array_filter($students, function ($student) use ($search) {
                return stripos($student['first_name'] . ' ' . $student['last_name'], $search) !== false
                    || stripos($student['student_id'] ?? '', $search) !== false;
            });
        }

        return view('admin.system_settings', [
            'user' => $user,
            'students' => $students,
            'search' => $search,
        ]);
    }

    public function deleteStudent(string $studentId)
    {
        $user = $this->authorizeAdmin();
        if (!$user) {
            return redirect('/login');
        }

        AttendanceStore::deleteUser($studentId);
        return back()->with('success', 'Student record deleted successfully.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Services\AttendanceStore;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class CoachController extends Controller
{
    protected function authorizeCoach()
    {
        $user = AttendanceStore::currentUser();
        if (!$user || $user['role'] !== 'coach') {
            return null;
        }
        return $user;
    }

    public function dashboard(Request $request)
    {
        $user = $this->authorizeCoach();
        if (!$user) {
            return redirect('/login');
        }

        $year = (int) $request->query('year', now()->year);
        $month = (int) $request->query('month', now()->month);
        $students = AttendanceStore::allStudentsStatus($user['sport'], $year, $month);
        $search = $request->query('search', '');
        if ($search) {
            $students = array_filter($students, function ($student) use ($search) {
                return stripos($student['first_name'] . ' ' . $student['last_name'], $search) !== false;
            });
        }

        $pendingExcuseCount = AttendanceStore::getPendingExcuseCount($user['sport']);
        $pendingSpecialTrainingCount = AttendanceStore::getPendingSpecialTrainingRequestCount($user['sport']);

        return view('coach.dashboard', [
            'user' => $user,
            'students' => $students,
            'year' => $year,
            'month' => $month,
            'search' => $search,
            'pendingExcuseCount' => $pendingExcuseCount,
            'pendingSpecialTrainingCount' => $pendingSpecialTrainingCount,
        ]);
    }

    public function schedule(Request $request, string $sport)
    {
        $user = $this->authorizeCoach();
        if (!$user || ($user['sport'] !== $sport && $user['sport'] !== 'all')) {
            return redirect('/coach/dashboard');
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

        return view('coach.schedule', [
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
        $user = $this->authorizeCoach();
        if (!$user || ($user['sport'] !== $sport && $user['sport'] !== 'all')) {
            return redirect('/coach/dashboard');
        }

        $request->validate([
            'date' => 'required|date',
            'session_type' => 'required|in:training,no_training',
            'venue' => 'required_if:session_type,training|string|max:120',
            'time' => 'required_if:session_type,training|string|max:30',
            'month' => 'nullable|string',
        ]);

        $sessionType = $request->input('session_type');
        if ($sessionType === 'no_training') {
            $time = 'No Training';
            $venue = 'Rest Day';
        } else {
            $time = $request->input('time');
            $venue = $request->input('venue');
        }

        // Prevent double-booking: check for existing schedule on the same date/venue for another sport
        if ($sessionType === 'training') {
            $conflict = AttendanceStore::findScheduleConflict($sport, $request->input('date'), $time, $venue);
            if ($conflict) {
                return back()->withErrors(['date' => "Schedule conflict: {$conflict['sport']} already has a booking at {$venue} on {$request->input('date')}."])->withInput();
            }
        }

        AttendanceStore::setSchedule($sport, $request->input('date'), $time, $venue);
        $monthKey = $request->input('month', now()->format('Y-m'));

        return redirect("/coach/schedule/{$sport}?month={$monthKey}")->with('success', 'Training schedule updated.');
    }

    public function updateSchedule(Request $request, string $sport, string $date)
    {
        $user = $this->authorizeCoach();
        if (!$user || ($user['sport'] !== $sport && $user['sport'] !== 'all')) {
            return redirect('/coach/dashboard');
        }

        $request->validate([
            'date' => 'required|date',
            'session_type' => 'required|in:training,no_training',
            'venue' => 'required_if:session_type,training|string|max:120',
            'time' => 'required_if:session_type,training|string|max:30',
            'month' => 'nullable|string',
        ]);

        $sessionType = $request->input('session_type');
        if ($sessionType === 'no_training') {
            $time = 'No Training';
            $venue = 'Rest Day';
        } else {
            $time = $request->input('time');
            $venue = $request->input('venue');
        }

        // Prevent double-booking when updating: check for conflicts on the new date/venue
        if ($sessionType === 'training') {
            $conflict = AttendanceStore::findScheduleConflict($sport, $request->input('date'), $request->input('time'), $request->input('venue'));
            if ($conflict) {
                return back()->withErrors(['date' => "Schedule conflict: {$conflict['sport']} already has a booking at {$request->input('venue')} on {$request->input('date')}."])->withInput();
            }
        }

        AttendanceStore::updateSchedule($sport, $date, $request->input('date'), $time, $venue);
        $monthKey = $request->input('month', now()->format('Y-m'));

        return redirect("/coach/schedule/{$sport}?month={$monthKey}")->with('success', 'Schedule entry updated.');
    }

    public function publishSchedule(Request $request, string $sport)
    {
        $user = $this->authorizeCoach();
        if (!$user || ($user['sport'] !== $sport && $user['sport'] !== 'all')) {
            return redirect('/coach/dashboard');
        }

        $request->validate(['month' => 'nullable|string']);
        AttendanceStore::publishSchedule($sport);
        $monthKey = $request->input('month', now()->format('Y-m'));

        return redirect("/coach/schedule/{$sport}?month={$monthKey}")->with('success', 'Schedule posted and students notified.');
    }

    public function deleteSchedule(Request $request, string $sport, string $date)
    {
        $user = $this->authorizeCoach();
        if (!$user || ($user['sport'] !== $sport && $user['sport'] !== 'all')) {
            return redirect('/coach/dashboard');
        }

        AttendanceStore::deleteSchedule($sport, $date);
        return back()->with('success', 'Schedule entry deleted.');
    }

    public function announcements(Request $request)
    {
        $user = $this->authorizeCoach();
        if (!$user) {
            return redirect('/login');
        }

        $announcements = AttendanceStore::getAnnouncements($user['sport']);
        return view('coach.announcements', [
            'user' => $user,
            'announcements' => $announcements,
        ]);
    }

    public function updateAnnouncement(Request $request, string $announcementId)
    {
        $user = $this->authorizeCoach();
        if (!$user) {
            return redirect('/login');
        }

        $request->validate([
            'title' => 'required|string|max:120',
            'body' => 'required|string|max:500',
            'sport' => 'required|in:all,taekwondo,karatedo',
        ]);

        AttendanceStore::updateAnnouncement($announcementId, $request->input('sport'), $request->input('title'), $request->input('body'));
        return back()->with('success', 'Announcement updated successfully.');
    }

    public function deleteAnnouncement(string $announcementId)
    {
        $user = $this->authorizeCoach();
        if (!$user) {
            return redirect('/login');
        }

        AttendanceStore::deleteAnnouncement($announcementId);
        return back()->with('success', 'Announcement deleted successfully.');
    }

    public function createAnnouncement(Request $request)
    {
        $user = $this->authorizeCoach();
        if (!$user) {
            return redirect('/login');
        }

        $request->validate([
            'title' => 'required|string|max:120',
            'body' => 'required|string|max:500',
        ]);

        AttendanceStore::addAnnouncement($user['sport'], $request->input('title'), $request->input('body'));
        return back()->with('success', 'Announcement posted to your team.');
    }

    public function excuseList()
    {
        $user = $this->authorizeCoach();
        if (!$user) {
            return redirect('/login');
        }

        $excuses = AttendanceStore::getExcuses($user['sport']);
        return view('coach.excuses', [
            'user' => $user,
            'excuses' => $excuses,
        ]);
    }

    public function showExcuseRequest(string $id)
    {
        $user = $this->authorizeCoach();
        if (!$user) {
            return redirect('/login');
        }

        $excuse = AttendanceStore::findExcuseById($id);
        if (!$excuse || ($user['sport'] !== 'all' && ($excuse['sport'] ?? null) !== $user['sport'])) {
            return redirect('/coach/excuses')->withErrors(['excuse' => 'Excuse request not found.']);
        }

        $student = AttendanceStore::findUserByStudentId($excuse['student_id']);
        $excuse['student_display'] = $student
            ? trim($student['first_name'] . ' ' . $student['last_name']) . ' · ' . $excuse['student_id']
            : 'Student ID: ' . $excuse['student_id'];

        $excuse['submitted_at_label'] = !empty($excuse['submitted_at'])
            ? date('M j, Y', strtotime($excuse['submitted_at']))
            : null;

        return view('coach.excuse_detail', [
            'user' => $user,
            'excuse' => $excuse,
        ]);
    }

    public function specialTrainingList()
    {
        $user = $this->authorizeCoach();
        if (!$user) {
            return redirect('/login');
        }

        $requests = AttendanceStore::getPendingSpecialTrainingRequests($user['sport']);
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

        return view('coach.special_training', [
            'user' => $user,
            'requests' => $requests,
        ]);
    }

    public function showSpecialTrainingRequest(string $id)
    {
        $user = $this->authorizeCoach();
        if (!$user) {
            return redirect('/login');
        }

        $request = AttendanceStore::getSpecialTrainingRequestById($id);
        if (!$request || ($user['sport'] !== 'all' && ($request['sport'] ?? null) !== $user['sport'])) {
            return redirect('/coach/special-training')->withErrors(['request' => 'Special training request not found.']);
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

        return view('coach.special_training_detail', [
            'user' => $user,
            'request' => $request,
        ]);
    }

    public function approveExcuse($id)
    {
        $user = $this->authorizeCoach();
        if (!$user) {
            return redirect('/login');
        }

        AttendanceStore::approveExcuse($id);
        return back()->with('success', 'Excuse request has been approved.');
    }

    public function approveSpecialTraining($id)
    {
        $user = $this->authorizeCoach();
        if (!$user) {
            return redirect('/login');
        }

        AttendanceStore::approveSpecialTraining($id);
        return back()->with('success', 'Special training request has been approved.');
    }

    public function athleteProfile(Request $request, string $studentId)
    {
        $user = $this->authorizeCoach();
        if (!$user) {
            return redirect('/login');
        }

        $student = AttendanceStore::findUserByStudentId($studentId);
        if (!$student || $student['sport'] !== $user['sport']) {
            return redirect('/coach/dashboard');
        }

        $year = (int) $request->query('year', now()->year);
        $month = (int) $request->query('month', now()->month);
        $calendar = AttendanceStore::getMonthlyStatusCalendar($studentId, $year, $month);
        $counts = AttendanceStore::getMonthCounts($studentId, $year, $month);

        return view('coach.athlete', [
            'user' => $user,
            'student' => $student,
            'calendar' => $calendar,
            'counts' => $counts,
            'year' => $year,
            'month' => $month,
        ]);
    }
}

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\CoachController;
use App\Http\Controllers\AdminController;

Route::get('/', function () {
    return redirect('/products');
});
Route::get('/', [AttendanceController::class, 'welcome']);
Route::get('/login', [AttendanceController::class, 'showLogin']);
Route::post('/login', [AttendanceController::class, 'login']);
Route::get('/register', [AttendanceController::class, 'showRegister']);
Route::post('/register', [AttendanceController::class, 'register']);
Route::get('/logout', [AttendanceController::class, 'logout']);
Route::get('/profile', [AttendanceController::class, 'showProfile'])->name('profile.show');
Route::post('/profile', [AttendanceController::class, 'updateProfile'])->name('profile.update');

Route::get('/student/dashboard', [AttendanceController::class, 'studentDashboard']);
Route::post('/student/attend', [AttendanceController::class, 'studentAttend']);
Route::get('/student/excuse', [AttendanceController::class, 'showExcuse']);
Route::post('/student/excuse', [AttendanceController::class, 'sendExcuse']);
Route::get('/student/special-training', [AttendanceController::class, 'showSpecialTraining']);
Route::post('/student/special-training', [AttendanceController::class, 'sendSpecialTraining']);
Route::get('/student/roster', [AttendanceController::class, 'studentRoster']);
Route::get('/student/schedule', [AttendanceController::class, 'studentSchedule']);
Route::get('/student/announcements', [AttendanceController::class, 'studentAnnouncements']);
Route::post('/student/notifications/acknowledge/{type}', [AttendanceController::class, 'acknowledgeNotification']);

Route::get('/coach/dashboard', [CoachController::class, 'dashboard']);
Route::get('/coach/schedule/{sport}', [CoachController::class, 'schedule']);
Route::post('/coach/schedule/{sport}', [CoachController::class, 'storeSchedule']);
Route::post('/coach/schedule/{sport}/publish', [CoachController::class, 'publishSchedule']);
Route::post('/coach/schedule/{sport}/{date}/update', [CoachController::class, 'updateSchedule']);
Route::post('/coach/schedule/{sport}/{date}/delete', [CoachController::class, 'deleteSchedule']);
Route::get('/coach/announcements', [CoachController::class, 'announcements']);
Route::post('/coach/announcements', [CoachController::class, 'createAnnouncement']);
Route::post('/coach/announcements/{announcementId}/update', [CoachController::class, 'updateAnnouncement']);
Route::post('/coach/announcements/{announcementId}/delete', [CoachController::class, 'deleteAnnouncement']);
Route::get('/coach/excuses', [CoachController::class, 'excuseList']);
Route::get('/coach/excuses/{id}', [CoachController::class, 'showExcuseRequest']);
Route::post('/coach/excuses/{id}/approve', [CoachController::class, 'approveExcuse']);
Route::get('/coach/special-training', [CoachController::class, 'specialTrainingList']);
Route::get('/coach/special-training/{id}', [CoachController::class, 'showSpecialTrainingRequest']);
Route::post('/coach/special-training/{id}/approve', [CoachController::class, 'approveSpecialTraining']);
Route::get('/coach/athlete/{studentId}', [CoachController::class, 'athleteProfile']);

Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);
Route::get('/admin/create-coach', [AdminController::class, 'showCreateCoach']);
Route::get('/admin/coaches', [AdminController::class, 'coaches']);
Route::get('/admin/coach/{id}/edit', [AdminController::class, 'editCoach']);
Route::post('/admin/coach/{id}/update', [AdminController::class, 'updateCoach']);
Route::post('/admin/coach/{id}/delete', [AdminController::class, 'deleteCoach']);
Route::post('/admin/create-coach', [AdminController::class, 'createCoach']);
Route::get('/admin/schedule/{sport}', [AdminController::class, 'schedule']);
Route::post('/admin/schedule/{sport}', [AdminController::class, 'storeSchedule']);
Route::post('/admin/schedule/{sport}/{date}/update', [AdminController::class, 'updateSchedule']);
Route::post('/admin/schedule/{sport}/{date}/delete', [AdminController::class, 'deleteSchedule']);
Route::get('/admin/excuses', [AdminController::class, 'excuses']);
Route::get('/admin/excuses/{id}', [AdminController::class, 'showExcuseRequest']);
Route::post('/admin/excuses/{excuseId}/approve', [AdminController::class, 'approveExcuse']);
Route::get('/admin/special-training', [AdminController::class, 'specialTrainingList']);
Route::get('/admin/special-training/{id}', [AdminController::class, 'showSpecialTrainingRequest']);
Route::post('/admin/special-training/{id}/approve', [AdminController::class, 'approveSpecialTraining']);
Route::get('/admin/announcements', [AdminController::class, 'announcements']);
Route::post('/admin/announcements', [AdminController::class, 'createAnnouncement']);
Route::get('/admin/system-settings', [AdminController::class, 'systemSettings']);
Route::get('/admin/student/{studentId}/edit', [AdminController::class, 'editStudent']);
Route::post('/admin/student/{studentId}/attendance', [AdminController::class, 'updateStudentAttendance']);
Route::post('/admin/student/{studentId}/delete', [AdminController::class, 'deleteStudent']);

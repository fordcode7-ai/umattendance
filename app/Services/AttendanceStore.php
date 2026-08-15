<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class AttendanceStore
{
    protected static array $cache = [];
    protected static bool $cacheLoaded = false;

    protected static function dataPath(): string
    {
        return storage_path('app/attendance_data.json');
    }

    protected static function appTimezone(): string
    {
        return config('app.timezone') ?: date_default_timezone_get();
    }

    protected static function defaultSystemData(): array
    {
        return [
            'users' => [
                [
                    'id' => 'admin00',
                    'student_id' => null,
                    'email' => 'admin00@gmail.com',
                    'password' => 'taekwondo_karate',
                    'role' => 'admin',
                    'first_name' => 'Super',
                    'middle_name' => '',
                    'last_name' => 'Admin',
                    'year_level' => null,
                    'course' => null,
                    'contact' => null,
                    'sport' => 'none',
                    'avatar' => null,
                ],
                [
                    'id' => 'coach_taekwondo_1',
                    'student_id' => null,
                    'email' => 'coach.taekwondo@gmail.com',
                    'password' => 'coach123',
                    'role' => 'coach',
                    'first_name' => 'Coach',
                    'middle_name' => 'T',
                    'last_name' => 'Taekwondo',
                    'year_level' => null,
                    'course' => null,
                    'contact' => '09171234567',
                    'sport' => 'taekwondo',
                    'avatar' => null,
                ],
                [
                    'id' => 'coach_karatedo_1',
                    'student_id' => null,
                    'email' => 'coach.karate@gmail.com',
                    'password' => 'coach123',
                    'role' => 'coach',
                    'first_name' => 'Coach',
                    'middle_name' => 'K',
                    'last_name' => 'Karatedo',
                    'year_level' => null,
                    'course' => null,
                    'contact' => '09179876543',
                    'sport' => 'karatedo',
                    'avatar' => null,
                ],
            ],
            'attendance' => [],
            'excuses' => [],
            'schedules' => [
                'taekwondo' => [],
                'karatedo' => [],
            ],
            'announcements' => [],
            'schedule_updates' => [],
            'schedule_draft_updates' => [],
            'notification_states' => [],
            'special_training_requests' => [],
        ];
    }

    protected static function databaseReady(): bool
    {
        try {
            return Schema::hasTable('attendance_users')
                && Schema::hasTable('attendance_records')
                && Schema::hasTable('attendance_excuses')
                && Schema::hasTable('attendance_schedules')
                && Schema::hasTable('attendance_announcements');
        } catch (\Exception $e) {
            self::writeLog('error', 'Database readiness check failed: ' . $e->getMessage());
            return false;
        }
    }

    protected static function databaseHasData(): bool
    {
        try {
            if (!self::databaseReady()) {
                return false;
            }

            return DB::table('attendance_users')->exists()
                || DB::table('attendance_records')->exists()
                || DB::table('attendance_excuses')->exists()
                || DB::table('attendance_schedules')->exists()
                || DB::table('attendance_announcements')->exists();
        } catch (\Exception $e) {
            self::writeLog('error', 'Database data check failed: ' . $e->getMessage());
            return false;
        }
    }

    protected static function legacyDataFromDatabase(): array
    {
        $data = self::defaultSystemData();

        $users = DB::table('attendance_users')->orderBy('created_at')->get();
        $data['users'] = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'student_id' => $user->student_id,
                'email' => $user->email,
                'password' => $user->password,
                'role' => $user->role,
                'first_name' => $user->first_name,
                'middle_name' => $user->middle_name ?? '',
                'last_name' => $user->last_name,
                'year_level' => $user->year_level,
                'course' => $user->course,
                'contact' => $user->contact,
                'sport' => $user->sport,
                'avatar' => $user->avatar,
                'title' => $user->title ?? '',
                'sports_handled' => is_string($user->sports_handled ?? null) ? json_decode($user->sports_handled, true) ?? [] : ($user->sports_handled ?? []),
            ];
        })->all();

        $attendanceRows = DB::table('attendance_records')->get();
        foreach ($attendanceRows as $entry) {
            $data['attendance'][$entry->student_id][$entry->date] = [
                'status' => $entry->status,
                'time' => $entry->time,
                'note' => $entry->note,
                'sport' => $entry->sport,
            ];
        }

        $data['excuses'] = DB::table('attendance_excuses')->orderBy('submitted_at')->get()->map(function ($entry) {
            return [
                'id' => $entry->id,
                'student_id' => $entry->student_id,
                'date' => $entry->date,
                'reason' => $entry->reason,
                'attachments' => is_string($entry->attachments) ? json_decode($entry->attachments, true) ?? [] : ($entry->attachments ?? []),
                'sport' => $entry->sport,
                'approved' => (bool) $entry->approved,
                'submitted_at' => $entry->submitted_at,
                'approved_at' => $entry->approved_at,
            ];
        })->all();

        $scheduleRows = DB::table('attendance_schedules')->orderBy('date')->get();
        foreach ($scheduleRows as $entry) {
            $sport = $entry->sport;
            if (!isset($data['schedules'][$sport])) {
                $data['schedules'][$sport] = [];
            }
            $data['schedules'][$sport][$entry->date] = [
                'time' => $entry->time,
                'venue' => $entry->venue,
                'created_at' => $entry->created_at,
            ];
        }

        $data['announcements'] = DB::table('attendance_announcements')->orderBy('created_at')->get()->map(function ($entry) {
            return [
                'id' => $entry->id,
                'sport' => $entry->sport,
                'title' => $entry->title,
                'body' => $entry->body,
                'created_at' => $entry->created_at,
                'updated_at' => $entry->updated_at,
            ];
        })->all();

        $settings = DB::table('attendance_system_settings')->get();
        foreach ($settings as $setting) {
            $value = $setting->setting_value;
            $decoded = json_decode($value, true);
            $data[$setting->setting_key] = is_array($decoded) ? $decoded : $value;
        }

        self::cleanupExpiredPendingRequests($data);
        self::normalizeUserAvatarPaths($data);
        return $data;
    }

    public static function migrateLegacyJsonToDatabase(): void
    {
        $path = self::dataPath();
        $legacy = [];
        if (file_exists($path)) {
            $raw = @file_get_contents($path);
            if ($raw !== false && trim($raw) !== '') {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    $legacy = $decoded;
                }
            }
        }

        if (empty($legacy)) {
            $legacy = self::defaultSystemData();
        }

        self::save($legacy, false);
    }

    public static function all(): array
    {
        if (self::$cacheLoaded) {
            return self::$cache;
        }

        $path = self::dataPath();
        if (self::databaseReady()) {
            if (self::databaseHasData()) {
                $data = self::legacyDataFromDatabase();
            } elseif (file_exists($path) && filesize($path) > 0) {
                self::migrateLegacyJsonToDatabase();
                $data = self::legacyDataFromDatabase();
            } else {
                $data = self::defaultSystemData();
                self::$cache = $data;
                self::$cacheLoaded = true;
                self::save($data, false);
                return self::$cache;
            }

            self::$cache = $data;
            self::$cacheLoaded = true;
            return $data;
        }

        if (!file_exists($path)) {
            $default = self::defaultSystemData();
            self::$cache = $default;
            self::$cacheLoaded = true;
            self::save($default, false);
            return self::$cache;
        }

        $raw = @file_get_contents($path);
        if ($raw === false || trim($raw) === '') {
            self::$cache = [];
            self::$cacheLoaded = true;
            return self::$cache;
        }

        $data = json_decode($raw, true);
        $data = is_array($data) ? $data : [];
        self::normalizeUserAvatarPaths($data);
        self::cleanupExpiredPendingRequests($data);
        self::$cache = $data;
        self::$cacheLoaded = true;
        return self::$cache;
    }

    public static function save(array $data, bool $withBackup = false): void
    {
        self::normalizeUserAvatarPaths($data);
        self::cleanupExpiredPendingRequests($data);

        if (self::databaseReady()) {
            DB::transaction(function () use ($data) {
                DB::table('attendance_users')->delete();
                foreach ($data['users'] ?? [] as $user) {
                    DB::table('attendance_users')->insert([
                        'id' => $user['id'],
                        'student_id' => $user['student_id'],
                        'email' => $user['email'],
                        'password' => $user['password'],
                        'role' => $user['role'],
                        'first_name' => $user['first_name'],
                        'middle_name' => $user['middle_name'] ?? '',
                        'last_name' => $user['last_name'],
                        'year_level' => $user['year_level'] ?? null,
                        'course' => $user['course'] ?? null,
                        'contact' => $user['contact'] ?? null,
                        'sport' => $user['sport'] ?? null,
                        'avatar' => $user['avatar'] ?? null,
                        'title' => $user['title'] ?? null,
                        'sports_handled' => isset($user['sports_handled']) ? json_encode($user['sports_handled']) : null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                DB::table('attendance_records')->delete();
                foreach ($data['attendance'] ?? [] as $studentId => $records) {
                    foreach ($records as $date => $entry) {
                        DB::table('attendance_records')->insert([
                            'student_id' => $studentId,
                            'date' => $date,
                            'status' => $entry['status'] ?? 'absent',
                            'time' => $entry['time'] ?? null,
                            'note' => $entry['note'] ?? '',
                            'sport' => $entry['sport'] ?? 'taekwondo',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                DB::table('attendance_excuses')->delete();
                foreach ($data['excuses'] ?? [] as $excuse) {
                    DB::table('attendance_excuses')->insert([
                        'id' => $excuse['id'],
                        'student_id' => $excuse['student_id'],
                        'date' => $excuse['date'],
                        'reason' => $excuse['reason'],
                        'attachments' => json_encode($excuse['attachments'] ?? []),
                        'sport' => $excuse['sport'],
                        'approved' => !empty($excuse['approved']) ? 1 : 0,
                        'submitted_at' => $excuse['submitted_at'] ?? now(),
                        'approved_at' => $excuse['approved_at'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                DB::table('attendance_schedules')->delete();
                foreach ($data['schedules'] ?? [] as $sport => $entries) {
                    foreach ($entries as $date => $entry) {
                        DB::table('attendance_schedules')->insert([
                            'sport' => $sport,
                            'date' => $date,
                            'time' => $entry['time'] ?? null,
                            'venue' => $entry['venue'] ?? null,
                            'created_at' => $entry['created_at'] ?? now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                DB::table('attendance_announcements')->delete();
                foreach ($data['announcements'] ?? [] as $announcement) {
                    DB::table('attendance_announcements')->insert([
                        'id' => $announcement['id'],
                        'sport' => $announcement['sport'],
                        'title' => $announcement['title'],
                        'body' => $announcement['body'],
                        'created_at' => $announcement['created_at'] ?? now(),
                        'updated_at' => $announcement['updated_at'] ?? now(),
                    ]);
                }

                DB::table('attendance_system_settings')->delete();
                foreach (['schedule_updates', 'schedule_draft_updates', 'notification_states', 'special_training_requests'] as $key) {
                    if (!array_key_exists($key, $data)) {
                        continue;
                    }
                    DB::table('attendance_system_settings')->insert([
                        'setting_key' => $key,
                        'setting_value' => json_encode($data[$key]),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            });

            self::$cache = $data;
            self::$cacheLoaded = true;
            return;
        }

        $path = self::dataPath();
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $lockFile = $path . '.lock';
        $lockHandle = fopen($lockFile, 'c');
        if ($lockHandle === false) {
            $lockHandle = null;
        }

        if ($lockHandle !== null) {
            flock($lockHandle, LOCK_EX);
        }

        try {
            if ($withBackup && file_exists($path)) {
                $backupDir = storage_path('app/backups');
                if (!is_dir($backupDir)) {
                    mkdir($backupDir, 0755, true);
                }
                $stamp = date('Ymd_His');
                $backupPath = $backupDir . DIRECTORY_SEPARATOR . "attendance_data_{$stamp}.json";
                copy($path, $backupPath);
                $files = glob($backupDir . DIRECTORY_SEPARATOR . 'attendance_data_*.json');
                if ($files !== false) {
                    $threshold = strtotime('-30 days');
                    foreach ($files as $f) {
                        if (filemtime($f) < $threshold) {
                            @unlink($f);
                        }
                    }
                }
            }

            $tempPath = $path . '.tmp';
            $encoded = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            if ($encoded === false) {
                throw new \RuntimeException('Unable to encode attendance data.');
            }

            if (file_exists($path)) {
                @unlink($path);
            }
            file_put_contents($tempPath, $encoded, LOCK_EX);
            @rename($tempPath, $path);
            @chmod($path, 0644);

            self::$cache = $data;
            self::$cacheLoaded = true;
        } finally {
            if ($lockHandle !== null) {
                flock($lockHandle, LOCK_UN);
                fclose($lockHandle);
            }
        }
    }

    protected static function normalizeUserAvatarPaths(array &$data): void
    {
        foreach ($data['users'] as &$user) {
            if (!empty($user['avatar'])) {
                $user['avatar'] = self::normalizeAvatarPath($user['avatar']);
            }
        }
        unset($user);
    }

    protected static function normalizeAvatarPath(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        $path = trim($path);
        $path = preg_replace('#^/storage/storage/#', '/storage/', $path);
        $path = preg_replace('#^storage/#', '/storage/', $path);

        if (strpos($path, '/storage/') !== 0 && !preg_match('#^https?://#i', $path)) {
            $path = '/storage/' . ltrim($path, '/');
        }

        return $path;
    }

    protected static function cleanupExpiredPendingRequests(array &$data): void
    {
        $threshold = strtotime('-7 days');

        $data['excuses'] = array_values(array_filter($data['excuses'] ?? [], function ($item) use ($threshold) {
            if (!empty($item['approved'])) {
                return true;
            }
            $submittedAt = strtotime($item['submitted_at'] ?? '');
            if ($submittedAt === false) {
                return true;
            }
            return $submittedAt >= $threshold;
        }));

        $data['special_training_requests'] = array_values(array_filter($data['special_training_requests'] ?? [], function ($item) use ($threshold) {
            if (!empty($item['approved'])) {
                return true;
            }
            $submittedAt = strtotime($item['submitted_at'] ?? '');
            if ($submittedAt === false) {
                return true;
            }
            return $submittedAt >= $threshold;
        }));

        $data['announcements'] = array_values(array_filter($data['announcements'] ?? [], function ($item) use ($threshold) {
            $createdAt = strtotime($item['created_at'] ?? '');
            if ($createdAt === false) {
                return true;
            }
            return $createdAt >= $threshold;
        }));
    }

    protected static function writeLog(string $level, string $message): void
    {
        $logDir = storage_path('logs');
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        $logPath = $logDir . DIRECTORY_SEPARATOR . 'attendance.log';
        // rotate log if it exceeds 5MB
        if (file_exists($logPath) && filesize($logPath) > 5 * 1024 * 1024) {
            $rotated = $logDir . DIRECTORY_SEPARATOR . 'attendance_' . date('Ymd_His') . '.log';
            @rename($logPath, $rotated);
        }
        $time = date('Y-m-d H:i:s');
        $line = "[{$time}] {$level}: {$message}\n";
        file_put_contents($logPath, $line, FILE_APPEND | LOCK_EX);
    }

    public static function findUserByEmail(string $email): ?array
    {
        $data = self::all();
        foreach ($data['users'] as $user) {
            if (strtolower($user['email']) === strtolower($email)) {
                return $user;
            }
        }
        return null;
    }

    public static function findUserByStudentId(string $studentId): ?array
    {
        $studentId = trim($studentId);
        $normalizedId = strtolower($studentId);
        $data = self::all();

        foreach ($data['users'] as $user) {
            if (empty($user['student_id'])) {
                continue;
            }

            if (strtolower(trim($user['student_id'])) === $normalizedId) {
                return $user;
            }
        }

        return null;
    }

    public static function findUserById(string $id): ?array
    {
        $data = self::all();
        foreach ($data['users'] as $user) {
            if ($user['id'] === $id) {
                return $user;
            }
        }
        return null;
    }

    public static function addUser(array $user): array
    {
        $data = self::all();
        $data['users'][] = $user;
        self::save($data);
        return $user;
    }

    public static function allStudents(string $sport = null): array
    {
        $data = self::all();
        return array_values(array_filter($data['users'], function ($user) use ($sport) {
            return $user['role'] === 'student' && ($sport === null || $user['sport'] === $sport);
        }));
    }

    public static function allCoaches(): array
    {
        $data = self::all();
        return array_values(array_filter($data['users'], function ($user) {
            return $user['role'] === 'coach';
        }));
    }

    public static function currentUser(): ?array
    {
        return session('user');
    }

    public static function setUserAvatar(string $userId, ?string $path): void
    {
        if ($path !== null && !str_starts_with($path, '/storage/')) {
            $path = '/storage/' . ltrim($path, '/');
        }

        $data = self::all();
        foreach ($data['users'] as &$user) {
            if ($user['id'] === $userId) {
                $user['avatar'] = $path;
                break;
            }
        }
        self::save($data);
    }

    public static function updateUser(string $userId, array $updates): ?array
    {
        $data = self::all();
        foreach ($data['users'] as &$user) {
            if ($user['id'] === $userId) {
                $user = array_merge($user, $updates);
                self::save($data);
                return $user;
            }
        }
        return null;
    }

    public static function addAttendance(string $studentId, string $date, string $status, string $time, string $sport, string $note = ''): void
    {
        $data = self::all();
        if (!isset($data['attendance'][$studentId])) {
            $data['attendance'][$studentId] = [];
        }
        $data['attendance'][$studentId][$date] = [
            'status' => $status,
            'time' => $time,
            'note' => $note,
            'sport' => $sport,
        ];
        self::save($data);
    }

    public static function getAttendance(string $studentId): array
    {
        $data = self::all();
        return $data['attendance'][$studentId] ?? [];
    }

    public static function getMonthlyAttendance(string $studentId, int $year, int $month): array
    {
        $attendance = self::getAttendance($studentId);
        $items = [];
        foreach ($attendance as $date => $entry) {
            if (strpos($date, sprintf('%04d-%02d', $year, $month)) === 0) {
                $items[$date] = $entry;
            }
        }
        return $items;
    }

    public static function systemStartDate(): string
    {
        $timezone = self::appTimezone();
        $envDate = env('ATTENDANCE_START_DATE');
        if (!empty($envDate)) {
            try {
                return Carbon::parse($envDate, $timezone)->format('Y-m-d');
            } catch (\Exception $e) {
                // Ignore invalid env date and fall back to today.
            }
        }

        return Carbon::today($timezone)->format('Y-m-d');
    }

    public static function isBeforeStartDate(string $date): bool
    {
        $timezone = self::appTimezone();
        $entryDate = Carbon::parse($date, $timezone)->startOfDay();
        $startDate = Carbon::parse(self::systemStartDate(), $timezone)->startOfDay();
        return $entryDate->lt($startDate);
    }

    protected static function attendanceDateHasEnded(string $date): bool
    {
        $timezone = self::appTimezone();
        $dateEnd = Carbon::parse($date, $timezone)->endOfDay();
        return $dateEnd->lt(Carbon::now($timezone));
    }

    protected static function hasTrainingScheduleForDate(string $sport, string $date): bool
    {
        $timezone = self::appTimezone();
        $monthKey = Carbon::parse($date, $timezone)->format('Y-m');
        $schedule = self::getSchedules($sport, $monthKey);
        if (empty($schedule[$date])) {
            return false;
        }
        return self::parseTimeRange($date, $schedule[$date]['time']) !== null;
    }

    public static function getMonthCounts(string $studentId, int $year, int $month): array
    {
        $timezone = self::appTimezone();
        $user = self::findUserByStudentId($studentId);
        $sport = $user['sport'] ?? null;
        $attendance = self::getMonthlyAttendance($studentId, $year, $month);
        $startDate = Carbon::parse(self::systemStartDate(), $timezone)->startOfDay();
        $counts = ['present' => 0, 'late' => 0, 'absent' => 0, 'excuse' => 0, 'special_training' => 0, 'no_training' => 0];

        foreach ($attendance as $date => $entry) {
            $entryDate = Carbon::parse($date, $timezone)->startOfDay();
            if ($entryDate->lt($startDate)) {
                continue;
            }
            $status = $entry['status'] ?? 'absent';
            if (isset($counts[$status])) {
                $counts[$status]++;
            }
        }

        if ($sport) {
            $schedule = self::getSchedules($sport, sprintf('%04d-%02d', $year, $month));
            foreach ($schedule as $date => $entry) {
                $scheduleDate = Carbon::parse($date, $timezone)->startOfDay();
                if ($scheduleDate->lt($startDate)) {
                    continue;
                }
                if (!isset($attendance[$date]) && self::hasScheduleElapsed($date, $entry['time'])) {
                    $counts['absent']++;
                }
            }
        }

        return $counts;
    }

    public static function getAttendanceForDate(string $studentId, string $date): ?array
    {
        return self::getAttendance($studentId)[$date] ?? null;
    }

    public static function addSpecialTrainingRequest(string $studentId, string $startDate, string $endDate, string $title, string $reason, array $attachments, string $sport): void
    {
        $data = self::all();
        if (!isset($data['special_training_requests'])) {
            $data['special_training_requests'] = [];
        }

        $stored = [];
        foreach ($attachments as $attachment) {
            if ($attachment instanceof UploadedFile && $attachment->isValid()) {
                $path = $attachment->store('special_training', 'public');
                $stored[] = $path;
            }
        }

        $data['special_training_requests'][] = [
            'id' => uniqid('st_'),
            'student_id' => $studentId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'title' => $title,
            'reason' => $reason,
            'attachments' => $stored,
            'sport' => $sport,
            'approved' => false,
            'submitted_at' => now()->toDateTimeString(),
        ];

        self::save($data);
    }

    public static function getPendingSpecialTrainingRequests(?string $sport = null): array
    {
        $data = self::all();
        return array_values(array_filter($data['special_training_requests'] ?? [], function ($item) use ($sport) {
            return empty($item['approved']) && ($sport === null || $sport === 'all' || ($item['sport'] ?? null) === $sport);
        }));
    }

    public static function getSpecialTrainingRequestById(string $requestId): ?array
    {
        $data = self::all();
        foreach ($data['special_training_requests'] ?? [] as $request) {
            if (($request['id'] ?? null) === $requestId) {
                return $request;
            }
        }

        return null;
    }

    public static function approveSpecialTraining(string $requestId): void
    {
        $data = self::all();
        foreach ($data['special_training_requests'] ?? [] as $index => $request) {
            if (($request['id'] ?? null) !== $requestId) {
                continue;
            }

            $data['special_training_requests'][$index]['approved'] = true;
            $data['special_training_requests'][$index]['approved_at'] = now()->toDateTimeString();

            $current = strtotime($request['start_date']);
            $end = strtotime($request['end_date']);
            while ($current <= $end) {
                $date = date('Y-m-d', $current);
                $dayOfWeek = date('w', $current); // 0=Sunday, 6=Saturday

                if (!isset($data['attendance'][$request['student_id']])) {
                    $data['attendance'][$request['student_id']] = [];
                }

                // Saturday (6) is skipped, Sunday (0) is marked as no_training
                if ($dayOfWeek == 6) {
                    // Skip Saturday
                } elseif ($dayOfWeek == 0) {
                    // Sunday is no training day
                    $data['attendance'][$request['student_id']][$date] = [
                        'status' => 'no_training',
                        'time' => '--',
                        'note' => 'No training day (Sunday)',
                        'sport' => $request['sport'] ?? 'taekwondo',
                    ];
                } else {
                    // Weekday: apply special training
                    $data['attendance'][$request['student_id']][$date] = [
                        'status' => 'special_training',
                        'time' => '--',
                        'note' => 'Special training approved',
                        'sport' => $request['sport'] ?? 'taekwondo',
                    ];
                }
                $current = strtotime('+1 day', $current);
            }

            break;
        }

        self::save($data);
    }

    public static function autoMarkAbsentIfNeeded(string $studentId, string $sport, ?string $date = null): bool
    {
        $timezone = self::appTimezone();
        $date = $date ?? Carbon::today($timezone)->format('Y-m-d');
        if (self::isBeforeStartDate($date)) {
            return false;
        }

        if (!self::attendanceDateHasEnded($date)) {
            return false;
        }

        $dayOfWeek = Carbon::parse($date, $timezone)->dayOfWeek;
        if ($dayOfWeek === 0) {
            return false;
        }

        if (!self::hasTrainingScheduleForDate($sport, $date)) {
            return false;
        }

        $existing = self::getAttendanceForDate($studentId, $date);
        if ($existing) {
            return false;
        }

        self::addAttendance($studentId, $date, 'absent', '--', $sport, 'Auto-marked absent for missing check-in.');
        return true;
    }

    public static function autoMarkAbsentForMissingDailyCheckIns(?string $date = null): int
    {
        $timezone = self::appTimezone();
        $date = $date ?? Carbon::today($timezone)->format('Y-m-d');
        if (self::isBeforeStartDate($date)) {
            return 0;
        }

        if (!self::attendanceDateHasEnded($date)) {
            return 0;
        }

        $dayOfWeek = Carbon::parse($date, $timezone)->dayOfWeek;
        if ($dayOfWeek === 0) {
            return 0;
        }

        $students = self::allStudents();
        $count = 0;

        foreach ($students as $student) {
            $studentId = $student['student_id'] ?? null;
            $sport = $student['sport'] ?? null;

            if (!$studentId || !$sport) {
                continue;
            }

            if (!self::getAttendanceForDate($studentId, $date) && self::hasTrainingScheduleForDate($sport, $date)) {
                self::addAttendance($studentId, $date, 'absent', '--', $sport, 'Auto-marked absent for missing check-in.');
                $count++;
            }
        }

        return $count;
    }

    public static function getWeeklyRange(int $year, int $month): array
    {
        $timestamp = strtotime("{$year}-{$month}-01");
        $first = date('N', $timestamp);
        $days = date('t', $timestamp);
        return ['firstDow' => $first, 'days' => $days];
    }

    public static function getMonthlyStatusCalendar(string $studentId, int $year, int $month): array
    {
        $timezone = self::appTimezone();
        $attendance = self::getMonthlyAttendance($studentId, $year, $month);
        $calendar = [];
        $monthDays = Carbon::parse("{$year}-{$month}-01", $timezone)->daysInMonth;
        $sport = self::findUserByStudentId($studentId)['sport'] ?? null;
        $schedule = $sport ? self::getSchedules($sport, sprintf('%04d-%02d', $year, $month)) : [];
        $startDate = Carbon::parse(self::systemStartDate(), $timezone)->startOfDay();

        for ($day = 1; $day <= $monthDays; $day++) {
            $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
            $currentDate = Carbon::parse($date, $timezone)->startOfDay();
            if ($currentDate->lt($startDate)) {
                $calendar[$date] = null;
                continue;
            }

            $dayOfWeek = $currentDate->dayOfWeek; // 0=Sunday, 6=Saturday
            $entry = $attendance[$date] ?? null;

            // Auto-mark Sundays (0) as no_training if no record exists
            if (!$entry && $dayOfWeek == 0) {
                $entry = ['status' => 'no_training', 'time' => '--', 'note' => 'No training day (Sunday)'];
            } elseif (!$entry && isset($schedule[$date]) && self::hasScheduleElapsed($date, $schedule[$date]['time'])) {
                $entry = ['status' => 'absent', 'time' => '--', 'note' => 'No recorded check-in.'];
            }
            $calendar[$date] = $entry;
        }
        return $calendar;
    }

    public static function allStudentsStatus(?string $sport, int $year, int $month): array
    {
        $students = self::allStudents($sport);
        $list = [];
        foreach ($students as $student) {
            $counts = self::getMonthCounts($student['student_id'], $year, $month);
            $color = self::statusColor($counts);
            $list[] = array_merge($student, ['counts' => $counts, 'color' => $color]);
        }
        usort($list, function ($a, $b) {
            $aLast = strtolower($a['last_name'] ?? '');
            $bLast = strtolower($b['last_name'] ?? '');
            if ($aLast !== $bLast) {
                return $aLast <=> $bLast;
            }
            return strtolower($a['first_name'] ?? '') <=> strtolower($b['first_name'] ?? '');
        });
        return $list;
    }

    public static function statusColor(array $counts): string
    {
        if ($counts['absent'] >= 2) {
            return 'red';
        }
        if ($counts['late'] >= 3) {
            return 'yellow';
        }
        return 'green';
    }

    public static function getSchedules(string $sport, ?string $month = null): array
    {
        $data = self::all();
        $timezone = self::appTimezone();
        $monthKey = $month ?: Carbon::now($timezone)->format('Y-m');
        $items = $data['schedules'][$sport] ?? [];

        return array_filter($items, function ($item, $date) use ($monthKey, $timezone) {
            if (!is_string($date) || empty($date)) {
                return false;
            }

            return Carbon::parse($date, $timezone)->format('Y-m') === $monthKey;
        }, ARRAY_FILTER_USE_BOTH);
    }

    public static function getLatestSchedule(string $sport, ?string $month = null): ?array
    {
        $schedules = self::getSchedules($sport, $month);
        if (!$schedules) {
            return null;
        }

        uksort($schedules, function ($a, $b) {
            return strtotime($b) <=> strtotime($a);
        });

        $date = array_key_first($schedules);
        return [
            'date' => $date,
            'item' => $schedules[$date],
        ];
    }

    public static function getScheduleCount(string $sport): int
    {
        return count(self::getSchedules($sport));
    }

    public static function setSchedule(string $sport, string $date, string $time, string $venue): void
    {
        $data = self::all();
        if (!isset($data['schedules'][$sport])) {
            $data['schedules'][$sport] = [];
        }
        if (!isset($data['schedule_draft_updates'])) {
            $data['schedule_draft_updates'] = [];
        }

        $timestamp = now()->toDateTimeString();
        $data['schedule_draft_updates'][$sport] = $timestamp;
        $data['schedules'][$sport][$date] = [
            'time' => $time,
            'venue' => $venue,
            'created_at' => $timestamp,
        ];

        // Log schedule change
        $user = self::currentUser();
        $userId = $user['id'] ?? 'system';
        self::writeLog('info', "setSchedule by {$userId} for {$sport} on {$date} at {$venue} ({$time})");

        self::save($data);
    }

    /**
     * Check whether the requested schedule would conflict with another sport's schedule.
     * Returns an array with keys 'sport' and 'entry' if conflict found, otherwise null.
     */
    public static function findScheduleConflict(string $sport, string $date, ?string $time = null, ?string $venue = null): ?array
    {
        // Taekwondo and Karatedo may share the same venue on the same date.
        // Cross-sport checks should not prevent schedule creation.
        return null;
    }

    private static function parseTimeRange(string $date, string $time): ?array
    {
        $raw = trim((string)$time);
        if ($raw === '') {
            return null;
        }
        if (strcasecmp($raw, 'No Training') === 0 || strcasecmp($raw, 'Rest Day') === 0) {
            return null;
        }

        $parts = preg_split('/\s*-\s*/', $raw);
        if ($parts === false || count($parts) === 0) {
            return null;
        }

        $startStr = trim($parts[0]);
        $endStr = count($parts) > 1 ? trim($parts[1]) : '';
        $timezone = self::appTimezone();

        try {
            $start = Carbon::parse("{$date} {$startStr}", $timezone);
        } catch (\Exception $e) {
            return null;
        }

        $end = null;
        if ($endStr !== '') {
            try {
                $end = Carbon::parse("{$date} {$endStr}", $timezone);
            } catch (\Exception $e) {
                $end = null;
            }
        }

        if ($end === null || $end->lte($start)) {
            $end = $start->copy()->addHours(2);
        }

        return [$start->timestamp, $end->timestamp];
    }

    private static function hasScheduleElapsed(string $date, string $time): bool
    {
        $range = self::parseTimeRange($date, $time);
        if (!$range) {
            return false;
        }

        $now = Carbon::now(self::appTimezone())->timestamp;
        return $now >= $range[1];
    }

    private static function timesOverlap(int $aStart, int $aEnd, int $bStart, int $bEnd): bool
    {
        return ($aStart < $bEnd) && ($bStart < $aEnd);
    }

    public static function updateSchedule(string $sport, string $currentDate, string $date, string $time, string $venue): void
    {
        $data = self::all();
        if (!isset($data['schedules'][$sport][$currentDate])) {
            return;
        }

        if ($currentDate !== $date) {
            unset($data['schedules'][$sport][$currentDate]);
        }

        if (!isset($data['schedules'][$sport])) {
            $data['schedules'][$sport] = [];
        }
        if (!isset($data['schedule_draft_updates'])) {
            $data['schedule_draft_updates'] = [];
        }

        $timestamp = now()->toDateTimeString();
        $data['schedule_draft_updates'][$sport] = $timestamp;
        $data['schedules'][$sport][$date] = [
            'time' => $time,
            'venue' => $venue,
            'created_at' => $timestamp,
        ];

        $user = self::currentUser();
        $userId = $user['id'] ?? 'system';
        self::writeLog('info', "updateSchedule by {$userId} for {$sport}: {$currentDate} -> {$date} at {$venue} ({$time})");

        self::save($data);
    }

    public static function deleteSchedule(string $sport, string $date): void
    {
        $data = self::all();
        if (isset($data['schedules'][$sport][$date])) {
            unset($data['schedules'][$sport][$date]);
        }
        if (!isset($data['schedule_draft_updates'])) {
            $data['schedule_draft_updates'] = [];
        }
        $data['schedule_draft_updates'][$sport] = now()->toDateTimeString();
        $user = self::currentUser();
        $userId = $user['id'] ?? 'system';
        self::writeLog('info', "deleteSchedule by {$userId} for {$sport} on {$date}");
        self::save($data);
    }

    public static function publishSchedule(string $sport): void
    {
        $data = self::all();
        if (!isset($data['schedule_updates'])) {
            $data['schedule_updates'] = [];
        }
        if (!isset($data['schedule_draft_updates'])) {
            $data['schedule_draft_updates'] = [];
        }

        $timestamp = now()->toDateTimeString();
        $data['schedule_updates'][$sport] = $timestamp;
        $data['schedule_draft_updates'][$sport] = $timestamp;
        $user = self::currentUser();
        $userId = $user['id'] ?? 'system';
        self::writeLog('info', "publishSchedule by {$userId} for {$sport} at {$timestamp}");
        self::save($data);
    }

    public static function getScheduleUpdateTime(string $sport): ?string
    {
        $data = self::all();
        return $data['schedule_updates'][$sport] ?? null;
    }

    public static function hasPendingScheduleChanges(string $sport): bool
    {
        $data = self::all();
        $published = $data['schedule_updates'][$sport] ?? null;
        $draft = $data['schedule_draft_updates'][$sport] ?? null;

        if (!$draft) {
            return false;
        }
        if (!$published) {
            return true;
        }

        return strtotime($draft) > strtotime($published);
    }

    public static function getStudentNotificationState(string $studentId): array
    {
        $data = self::all();
        $state = $data['notification_states'][$studentId] ?? [];

        return [
            'schedule_seen_at' => $state['schedule_seen_at'] ?? null,
            'announcement_seen_at' => $state['announcement_seen_at'] ?? null,
            'excuse_acknowledged_at' => $state['excuse_acknowledged_at'] ?? null,
            'special_training_acknowledged_at' => $state['special_training_acknowledged_at'] ?? null,
        ];
    }

    public static function setStudentNotificationState(string $studentId, array $state): void
    {
        $data = self::all();
        if (!isset($data['notification_states'])) {
            $data['notification_states'] = [];
        }
        $data['notification_states'][$studentId] = array_merge($data['notification_states'][$studentId] ?? [], $state);
        self::save($data);
    }

    public static function hasUnreadScheduleNotifications(string $studentId, string $sport): bool
    {
        $state = self::getStudentNotificationState($studentId);
        $lastSeen = $state['schedule_seen_at'];

        $data = self::all();
        $latest = $data['schedule_updates'][$sport] ?? null;

        if (!$latest && isset($data['schedules'][$sport]) && count($data['schedules'][$sport]) > 0) {
            return $lastSeen === null;
        }

        if (!$latest) {
            return false;
        }

        return !$lastSeen || strtotime($latest) > strtotime($lastSeen);
    }

    public static function hasUnreadAnnouncementNotifications(string $studentId, string $sport): bool
    {
        $state = self::getStudentNotificationState($studentId);
        $lastSeen = $state['announcement_seen_at'];
        foreach (self::getAnnouncements($sport) as $announcement) {
            $createdAt = $announcement['created_at'] ?? null;
            if (!$createdAt) {
                if (!$lastSeen) {
                    return true;
                }
                continue;
            }
            if (!$lastSeen || strtotime($createdAt) > strtotime($lastSeen)) {
                return true;
            }
        }
        return false;
    }

    public static function getUnreadAnnouncementCount(string $studentId, string $sport): int
    {
        $count = 0;
        $state = self::getStudentNotificationState($studentId);
        $lastSeen = $state['announcement_seen_at'];

        foreach (self::getAnnouncements($sport) as $announcement) {
            $createdAt = $announcement['created_at'] ?? null;
            if (!$createdAt) {
                if (!$lastSeen) {
                    $count++;
                }
                continue;
            }
            if (!$lastSeen || strtotime($createdAt) > strtotime($lastSeen)) {
                $count++;
            }
        }

        return $count;
    }

    public static function getUnacknowledgedApprovedExcuses(string $studentId): array
    {
        $state = self::getStudentNotificationState($studentId);
        $acknowledgedAt = $state['excuse_acknowledged_at'];
        $data = self::all();
        $items = [];

        foreach ($data['excuses'] as $excuse) {
            if ($excuse['student_id'] !== $studentId || empty($excuse['approved'])) {
                continue;
            }

            $approvedAt = $excuse['approved_at'] ?? $excuse['submitted_at'] ?? null;
            if (!$approvedAt) {
                $approvedAt = now()->toDateTimeString();
            }

            if (!$acknowledgedAt || strtotime($approvedAt) > strtotime($acknowledgedAt)) {
                $excuse['approved_at'] = $approvedAt;
                $items[] = $excuse;
            }
        }

        return $items;
    }

    public static function getUnacknowledgedApprovedSpecialTraining(string $studentId): array
    {
        $state = self::getStudentNotificationState($studentId);
        $acknowledgedAt = $state['special_training_acknowledged_at'] ?? null;
        $data = self::all();
        $items = [];

        foreach ($data['special_training_requests'] ?? [] as $request) {
            if ($request['student_id'] !== $studentId || empty($request['approved'])) {
                continue;
            }

            $approvedAt = $request['approved_at'] ?? $request['submitted_at'] ?? null;
            if (!$approvedAt) {
                $approvedAt = now()->toDateTimeString();
            }

            if (!$acknowledgedAt || strtotime($approvedAt) > strtotime($acknowledgedAt)) {
                $request['approved_at'] = $approvedAt;
                $items[] = $request;
            }
        }

        return $items;
    }

    public static function getUnreadNotificationCount(string $studentId, string $sport): int
    {
        $count = 0;
        if (self::hasUnreadScheduleNotifications($studentId, $sport)) {
            $count++;
        }
        $count += self::getUnreadAnnouncementCount($studentId, $sport);
        $count += count(self::getUnacknowledgedApprovedExcuses($studentId));
        $count += count(self::getUnacknowledgedApprovedSpecialTraining($studentId));
        return $count;
    }

    public static function acknowledgeStudentNotification(string $studentId, string $type): void
    {
        $state = self::getStudentNotificationState($studentId);
        $timestamp = now()->toDateTimeString();

        if ($type === 'schedule') {
            $state['schedule_seen_at'] = $timestamp;
        } elseif ($type === 'announcement') {
            $state['announcement_seen_at'] = $timestamp;
        } elseif ($type === 'excuse') {
            $state['excuse_acknowledged_at'] = $timestamp;
        } elseif ($type === 'special_training') {
            $state['special_training_acknowledged_at'] = $timestamp;
        }

        self::setStudentNotificationState($studentId, $state);
    }

    public static function isNewItem(?string $timestamp, int $hours = 24): bool
    {
        if (empty($timestamp)) {
            return false;
        }

        $createdAt = strtotime($timestamp);
        if ($createdAt === false) {
            return false;
        }

        return (time() - $createdAt) <= ($hours * 3600);
    }

    public static function formatDisplayTime(?string $value): string
    {
        if (empty($value)) {
            return '--';
        }

        $segments = preg_split('/\s*-\s*/', trim((string) $value));
        if ($segments === false || count($segments) === 0) {
            return '--';
        }

        $formatted = array_map(function ($segment) {
            $segment = trim((string) $segment);
            if ($segment === '') {
                return '';
            }

            if (preg_match('/^(\d{1,2}):(\d{2})\s*(AM|PM)$/i', $segment, $matches)) {
                return date('g:i A', strtotime("{$matches[1]}:{$matches[2]} {$matches[3]}"));
            }

            if (preg_match('/^(\d{1,2}):(\d{2})$/', $segment, $matches)) {
                return date('g:i A', strtotime("{$matches[1]}:{$matches[2]}"));
            }

            return $segment;
        }, $segments);

        return implode(' - ', array_filter($formatted, static function ($segment) {
            return $segment !== '';
        }));
    }

    public static function getAnnouncements(string $sport, ?string $month = null): array
    {
        $data = self::all();
        $monthKey = $month ?: now()->format('Y-m');

        return array_values(array_filter($data['announcements'], function ($item) use ($sport, $monthKey) {
            if ($item['sport'] !== $sport && $item['sport'] !== 'all') {
                return false;
            }

            $createdAt = $item['created_at'] ?? null;
            if (!$createdAt) {
                return false;
            }

            return date('Y-m', strtotime($createdAt)) === $monthKey;
        }));
    }

    public static function getLatestAnnouncement(string $sport, ?string $month = null): ?array
    {
        $announcements = self::getAnnouncements($sport, $month);
        if (empty($announcements)) {
            return null;
        }

        usort($announcements, function ($a, $b) {
            return strtotime($b['created_at'] ?? '0') <=> strtotime($a['created_at'] ?? '0');
        });

        return $announcements[0];
    }

    public static function getAnnouncementCount(string $sport): int
    {
        return count(self::getAnnouncements($sport));
    }

    public static function getPendingExcuseCount(?string $sport = null): int
    {
        $data = self::all();
        $count = 0;
        foreach ($data['excuses'] as $excuse) {
            if ($excuse['approved']) {
                continue;
            }
            if ($sport === null || $sport === 'all' || $excuse['sport'] === $sport) {
                $count++;
            }
        }
        return $count;
    }

    public static function getPendingSpecialTrainingRequestCount(?string $sport = null): int
    {
        $data = self::all();
        $count = 0;
        foreach ($data['special_training_requests'] ?? [] as $request) {
            if (!empty($request['approved'])) {
                continue;
            }
            if ($sport === null || $sport === 'all' || ($request['sport'] ?? null) === $sport) {
                $count++;
            }
        }
        return $count;
    }

    public static function addAnnouncement(string $sport, string $title, string $body): void
    {
        $data = self::all();
        $data['announcements'][] = [
            'id' => uniqid('ann_'),
            'sport' => $sport,
            'title' => $title,
            'body' => $body,
            'created_at' => now()->toDateTimeString(),
        ];
        self::save($data);
    }

    public static function updateAnnouncement(string $announcementId, string $sport, string $title, string $body): void
    {
        $data = self::all();
        foreach ($data['announcements'] as &$announcement) {
            if ($announcement['id'] === $announcementId) {
                $announcement['sport'] = $sport;
                $announcement['title'] = $title;
                $announcement['body'] = $body;
                $announcement['updated_at'] = now()->toDateTimeString();
                break;
            }
        }
        self::save($data);
    }

    public static function deleteAnnouncement(string $announcementId): void
    {
        $data = self::all();
        $data['announcements'] = array_values(array_filter($data['announcements'], function ($announcement) use ($announcementId) {
            return $announcement['id'] !== $announcementId;
        }));
        self::save($data);
    }

    public static function addExcuse(string $studentId, string $date, string $title, string $reason, array $attachments, string $sport): void
    {
        $data = self::all();
        $stored = [];
        foreach ($attachments as $attachment) {
            if ($attachment instanceof UploadedFile && $attachment->isValid()) {
                $path = $attachment->store('excuses', 'public');
                $stored[] = $path;
            }
        }
        $data['excuses'][] = [
            'id' => uniqid('exc_'),
            'student_id' => $studentId,
            'date' => $date,
            'title' => $title,
            'reason' => $reason,
            'attachments' => $stored,
            'sport' => $sport,
            'approved' => false,
            'submitted_at' => now()->toDateTimeString(),
        ];
        self::save($data);
    }

    public static function getExcuses(string $sport): array
    {
        $data = self::all();
        return array_values(array_filter($data['excuses'], function ($item) use ($sport) {
            return ($item['sport'] === $sport || $item['sport'] === 'all') && !$item['approved'];
        }));
    }

    public static function findExcuseById(string $excuseId): ?array
    {
        $data = self::all();
        foreach ($data['excuses'] as $excuse) {
            if (($excuse['id'] ?? null) === $excuseId) {
                return $excuse;
            }
        }
        return null;
    }

    public static function approveExcuse(string $excuseId): void
    {
        $data = self::all();
        // Find the excuse and update it, and add the attendance record inside the same data array
        foreach ($data['excuses'] as $idx => $excuse) {
            if ($excuse['id'] === $excuseId) {
                $approvedAt = now()->toDateTimeString();
                $data['excuses'][$idx]['approved'] = true;
                $data['excuses'][$idx]['approved_at'] = $approvedAt;

                // Ensure attendance structure exists and write the excuse attendance atomically
                $studentId = $excuse['student_id'];
                $date = $excuse['date'];
                $sport = $excuse['sport'] ?? ($excuse['sport'] ?? 'taekwondo');
                if (!isset($data['attendance'][$studentId])) {
                    $data['attendance'][$studentId] = [];
                }
                $data['attendance'][$studentId][$date] = [
                    'status' => 'excuse',
                    'time' => now()->format('H:i'),
                    'note' => 'Excuse approved',
                    'sport' => $sport,
                ];

                break;
            }
        }

        self::save($data);
    }

    public static function updateAttendance(string $studentId, string $date, string $status, string $time = null): void
    {
        $existing = self::getAttendanceForDate($studentId, $date);
        $time = $time ?? $existing['time'] ?? now()->format('H:i');
        $sport = self::findUserByStudentId($studentId)['sport'] ?? 'taekwondo';
        self::addAttendance($studentId, $date, $status, $time, $sport, 'Updated by admin');
    }

    public static function deleteUser(string $studentId): void
    {
        $data = self::all();
        $data['users'] = array_values(array_filter($data['users'], function ($user) use ($studentId) {
            return $user['student_id'] !== $studentId;
        }));
        self::save($data);
    }

    public static function deleteUserById(string $id): void
    {
        $data = self::all();
        $data['users'] = array_values(array_filter($data['users'], function ($user) use ($id) {
            return ($user['id'] ?? null) !== $id;
        }));
        self::save($data);
    }
}

<?php

namespace Tests\Unit;

use App\Services\AttendanceStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceStoreTest extends TestCase
{
    use RefreshDatabase;

    protected $dataPath;
    protected $backupPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dataPath = storage_path('app/attendance_data.json');
        $this->backupPath = storage_path('app/attendance_data_test_backup.json');
        if (file_exists($this->dataPath)) {
            copy($this->dataPath, $this->backupPath);
        }
    }

    protected function tearDown(): void
    {
        if (file_exists($this->backupPath)) {
            copy($this->backupPath, $this->dataPath);
            @unlink($this->backupPath);
        }
        parent::tearDown();
    }

    public function testParseTimeRangeAndOverlap()
    {
        $ref = new \ReflectionClass(AttendanceStore::class);
        $parse = $ref->getMethod('parseTimeRange');
        $parse->setAccessible(true);
        $timesOverlap = $ref->getMethod('timesOverlap');
        $timesOverlap->setAccessible(true);

        $range = $parse->invokeArgs(null, ['2026-08-10', '8 am - 10 am']);
        $this->assertIsArray($range);
        $this->assertCount(2, $range);
        $this->assertLessThan($range[0] + 24 * 3600, $range[1]);

        $rangeB = $parse->invokeArgs(null, ['2026-08-10', '9:30 AM - 11:00 AM']);
        $this->assertTrue($timesOverlap->invokeArgs(null, [$range[0], $range[1], $rangeB[0], $rangeB[1]]));

        $rangeC = $parse->invokeArgs(null, ['2026-08-10', '11:01 AM - 12:00 PM']);
        $this->assertFalse($timesOverlap->invokeArgs(null, [$range[0], $range[1], $rangeC[0], $rangeC[1]]));
    }

    public function testFindScheduleConflictDetectsOverlap()
    {
        $data = [
            'users' => [],
            'attendance' => [],
            'excuses' => [],
            'schedules' => [
                'taekwondo' => [
                    '2026-08-12' => [
                        'time' => '8 am - 10 am',
                        'venue' => 'Main Gym',
                        'created_at' => '2026-08-01 00:00:00',
                    ],
                ],
                'karatedo' => [
                    '2026-08-12' => [
                        'time' => '9 am - 11 am',
                        'venue' => 'Main Gym',
                        'created_at' => '2026-08-01 00:00:00',
                    ],
                ],
            ],
            'announcements' => [],
        ];

        // write test data
        file_put_contents($this->dataPath, json_encode($data, JSON_PRETTY_PRINT));

        $conflict = AttendanceStore::findScheduleConflict('taekwondo', '2026-08-12', '9:15 AM - 9:45 AM', 'Main Gym');
        $this->assertNotNull($conflict);
        $this->assertEquals('karatedo', $conflict['sport']);
    }

    public function testSaveWithoutBackupSkipsBackupCreation()
    {
        $data = [
            'users' => [],
            'attendance' => [],
            'excuses' => [],
            'schedules' => [],
            'announcements' => [],
        ];

        $backupFilesBefore = glob(storage_path('app/backups/attendance_data_*.json')) ?: [];

        AttendanceStore::save($data, false);

        $backupFilesAfter = glob(storage_path('app/backups/attendance_data_*.json')) ?: [];
        $this->assertCount(count($backupFilesBefore), $backupFilesAfter);
        $this->assertFileExists($this->dataPath);
    }

    public function testDefaultStartDateIsTodayWhenNoEnvVarIsSet()
    {
        putenv('ATTENDANCE_START_DATE');
        unset($_ENV['ATTENDANCE_START_DATE']);
        unset($_SERVER['ATTENDANCE_START_DATE']);

        $this->assertSame(now()->format('Y-m-d'), AttendanceStore::systemStartDate());
    }

    public function testFindUserByStudentIdIsTrimmedAndCaseInsensitive()
    {
        $data = [
            'users' => [
                [
                    'id' => 'student_case',
                    'student_id' => 'UM123456',
                    'email' => null,
                    'password' => 'secret123',
                    'role' => 'student',
                    'first_name' => 'Case',
                    'middle_name' => '',
                    'last_name' => 'Tester',
                    'year_level' => '1st Year',
                    'course' => 'BSIT',
                    'contact' => '09171234567',
                    'sport' => 'taekwondo',
                    'avatar' => null,
                ],
            ],
            'attendance' => [],
            'excuses' => [],
            'schedules' => [],
            'announcements' => [],
            'special_training_requests' => [],
        ];

        file_put_contents($this->dataPath, json_encode($data, JSON_PRETTY_PRINT));

        $this->assertNotNull(AttendanceStore::findUserByStudentId('  um123456  '));
    }

    public function testAutoMarksAbsentWhenStudentHasNoCheckInForTheDay()
    {
        $data = [
            'users' => [
                [
                    'id' => 'student_auto_absent',
                    'student_id' => '2024001',
                    'email' => 'student.auto@example.com',
                    'password' => 'secret123',
                    'role' => 'student',
                    'first_name' => 'Auto',
                    'middle_name' => '',
                    'last_name' => 'Absent',
                    'year_level' => '2',
                    'course' => 'BSIT',
                    'contact' => '09123456789',
                    'sport' => 'taekwondo',
                    'avatar' => null,
                ],
            ],
            'attendance' => [],
            'excuses' => [],
            'schedules' => [],
            'announcements' => [],
        ];

        file_put_contents($this->dataPath, json_encode($data, JSON_PRETTY_PRINT));

        AttendanceStore::autoMarkAbsentIfNeeded('2024001', 'taekwondo', '2026-08-08');

        $entry = AttendanceStore::getAttendanceForDate('2024001', '2026-08-08');
        $this->assertNotNull($entry);
        $this->assertSame('absent', $entry['status']);
        $this->assertSame('Auto-marked absent for missing check-in.', $entry['note']);
    }

    public function testAutoMarksAllStudentsAbsentForDayWhenNoCheckInExists()
    {
        $data = [
            'users' => [
                [
                    'id' => 'student_a',
                    'student_id' => '2024001',
                    'email' => 'student.a@example.com',
                    'password' => 'secret123',
                    'role' => 'student',
                    'first_name' => 'Alice',
                    'middle_name' => '',
                    'last_name' => 'Student',
                    'year_level' => '2',
                    'course' => 'BSIT',
                    'contact' => '09123456789',
                    'sport' => 'taekwondo',
                    'avatar' => null,
                ],
                [
                    'id' => 'student_b',
                    'student_id' => '2024002',
                    'email' => 'student.b@example.com',
                    'password' => 'secret123',
                    'role' => 'student',
                    'first_name' => 'Bob',
                    'middle_name' => '',
                    'last_name' => 'Student',
                    'year_level' => '3',
                    'course' => 'BSCS',
                    'contact' => '09123456790',
                    'sport' => 'karatedo',
                    'avatar' => null,
                ],
                [
                    'id' => 'student_c',
                    'student_id' => '2024003',
                    'email' => 'student.c@example.com',
                    'password' => 'secret123',
                    'role' => 'student',
                    'first_name' => 'Carol',
                    'middle_name' => '',
                    'last_name' => 'Student',
                    'year_level' => '1',
                    'course' => 'BSBA',
                    'contact' => '09123456791',
                    'sport' => 'taekwondo',
                    'avatar' => null,
                ],
            ],
            'attendance' => [
                '2024003' => [
                    '2026-08-08' => ['status' => 'present', 'time' => '08:00', 'note' => 'Already checked in', 'sport' => 'taekwondo'],
                ],
            ],
            'excuses' => [],
            'schedules' => [],
            'announcements' => [],
        ];

        AttendanceStore::save($data, false);

        $count = AttendanceStore::autoMarkAbsentForMissingDailyCheckIns('2026-08-08');

        $this->assertSame(2, $count);
        $this->assertNotNull(AttendanceStore::getAttendanceForDate('2024001', '2026-08-08'));
        $this->assertNotNull(AttendanceStore::getAttendanceForDate('2024002', '2026-08-08'));
        $this->assertSame('present', AttendanceStore::getAttendanceForDate('2024003', '2026-08-08')['status']);
    }

    public function testMonthCountsIgnoreAttendanceBeforeSystemStartDate()
    {
        putenv('ATTENDANCE_START_DATE=2026-08-11');
        $_ENV['ATTENDANCE_START_DATE'] = '2026-08-11';
        $_SERVER['ATTENDANCE_START_DATE'] = '2026-08-11';

        $data = [
            'users' => [
                [
                    'id' => 'student_prestart',
                    'student_id' => '2024020',
                    'email' => 'student.prestart@example.com',
                    'password' => 'secret123',
                    'role' => 'student',
                    'first_name' => 'Pre',
                    'middle_name' => '',
                    'last_name' => 'Start',
                    'year_level' => '2',
                    'course' => 'BSIT',
                    'contact' => '09123456789',
                    'sport' => 'taekwondo',
                    'avatar' => null,
                ],
            ],
            'attendance' => [
                '2024020' => [
                    '2026-08-09' => ['status' => 'present', 'time' => '08:00', 'note' => 'Before start', 'sport' => 'taekwondo'],
                    '2026-08-11' => ['status' => 'present', 'time' => '08:15', 'note' => 'After start', 'sport' => 'taekwondo'],
                ],
            ],
            'excuses' => [],
            'schedules' => [],
            'announcements' => [],
            'special_training_requests' => [],
        ];

        AttendanceStore::save($data, false);

        $counts = AttendanceStore::getMonthCounts('2024020', 2026, 8);
        $this->assertSame(1, $counts['present']);
        $this->assertSame(0, $counts['absent']);
    }

    public function testMonthlyCalendarSkipsDatesBeforeSystemStartDate()
    {
        putenv('ATTENDANCE_START_DATE=2026-08-11');
        $_ENV['ATTENDANCE_START_DATE'] = '2026-08-11';
        $_SERVER['ATTENDANCE_START_DATE'] = '2026-08-11';

        $data = [
            'users' => [
                [
                    'id' => 'student_prestart_calendar',
                    'student_id' => '2024021',
                    'email' => 'student.prestart.calendar@example.com',
                    'password' => 'secret123',
                    'role' => 'student',
                    'first_name' => 'Calendar',
                    'middle_name' => '',
                    'last_name' => 'Start',
                    'year_level' => '2',
                    'course' => 'BSIT',
                    'contact' => '09123456789',
                    'sport' => 'taekwondo',
                    'avatar' => null,
                ],
            ],
            'attendance' => [
                '2024021' => [
                    '2026-08-09' => ['status' => 'present', 'time' => '08:00', 'note' => 'Before start', 'sport' => 'taekwondo'],
                ],
            ],
            'excuses' => [],
            'schedules' => [],
            'announcements' => [],
            'special_training_requests' => [],
        ];

        AttendanceStore::save($data, false);

        $calendar = AttendanceStore::getMonthlyStatusCalendar('2024021', 2026, 8);
        $this->assertNull($calendar['2026-08-09']);
        $this->assertNotNull($calendar['2026-08-11']);
    }

    public function testSpecialTrainingRequestsCanBeApprovedForDateRange()
    {
        $data = [
            'users' => [
                [
                    'id' => 'student_special_training',
                    'student_id' => '2024009',
                    'email' => 'student.special@example.com',
                    'password' => 'secret123',
                    'role' => 'student',
                    'first_name' => 'Special',
                    'middle_name' => '',
                    'last_name' => 'Student',
                    'year_level' => '2',
                    'course' => 'BSIT',
                    'contact' => '09123456789',
                    'sport' => 'taekwondo',
                    'avatar' => null,
                ],
            ],
            'attendance' => [],
            'excuses' => [],
            'schedules' => [],
            'announcements' => [],
            'special_training_requests' => [],
        ];

        AttendanceStore::save($data, false);
        // 2026-08-10 (Mon) to 2026-08-16 (Sun)
        // This range includes Saturday (2026-08-15) and Sunday (2026-08-16)
        AttendanceStore::addSpecialTrainingRequest('2024009', '2026-08-10', '2026-08-16', 'Training camp', 'Training camp', [], 'taekwondo');

        $requests = AttendanceStore::getPendingSpecialTrainingRequests('taekwondo');
        $this->assertCount(1, $requests);

        AttendanceStore::approveSpecialTraining($requests[0]['id']);

        // Weekdays should be special_training
        $this->assertSame('special_training', AttendanceStore::getAttendanceForDate('2024009', '2026-08-10')['status']);
        $this->assertSame('special_training', AttendanceStore::getAttendanceForDate('2024009', '2026-08-11')['status']);
        $this->assertSame('special_training', AttendanceStore::getAttendanceForDate('2024009', '2026-08-12')['status']);
        $this->assertSame('special_training', AttendanceStore::getAttendanceForDate('2024009', '2026-08-13')['status']);
        $this->assertSame('special_training', AttendanceStore::getAttendanceForDate('2024009', '2026-08-14')['status']);
        
        // Saturday should be skipped (no entry)
        $this->assertNull(AttendanceStore::getAttendanceForDate('2024009', '2026-08-15'));
        
        // Sunday should be marked as no_training
        $this->assertSame('no_training', AttendanceStore::getAttendanceForDate('2024009', '2026-08-16')['status']);
        
        // Check counts: 5 special_training
        $counts = AttendanceStore::getMonthCounts('2024009', 2026, 8);
        $this->assertSame(5, $counts['special_training']);
    }

    public function testAcknowledgedSpecialTrainingClearsNotificationCount()
    {
        $data = [
            'users' => [
                [
                    'id' => 'student_special_training_ack',
                    'student_id' => '2024011',
                    'email' => 'student.clear@example.com',
                    'password' => 'secret123',
                    'role' => 'student',
                    'first_name' => 'Clear',
                    'middle_name' => '',
                    'last_name' => 'Notification',
                    'year_level' => '2',
                    'course' => 'BSIT',
                    'contact' => '09123456789',
                    'sport' => 'taekwondo',
                    'avatar' => null,
                ],
            ],
            'attendance' => [],
            'excuses' => [],
            'schedules' => [],
            'announcements' => [],
            'special_training_requests' => [
                [
                    'id' => 'st_request_1',
                    'student_id' => '2024011',
                    'sport' => 'taekwondo',
                    'start_date' => '2026-08-10',
                    'end_date' => '2026-08-12',
                    'reason' => 'Tournament prep',
                    'submitted_at' => '2026-08-08 10:00:00',
                    'approved' => true,
                    'approved_at' => '2026-08-08 12:00:00',
                ],
            ],
            'notification_states' => [],
        ];

        AttendanceStore::save($data, false);

        $this->assertCount(1, AttendanceStore::getUnacknowledgedApprovedSpecialTraining('2024011'));
        AttendanceStore::acknowledgeStudentNotification('2024011', 'special_training');
        $this->assertSame(0, count(AttendanceStore::getUnacknowledgedApprovedSpecialTraining('2024011')));

        $state = AttendanceStore::getStudentNotificationState('2024011');
        $this->assertArrayHasKey('special_training_acknowledged_at', $state);
        $this->assertNotNull($state['special_training_acknowledged_at']);
    }

    public function testMigratesLegacyJsonDataToDatabase()
    {
        $jsonPath = storage_path('app/attendance_data.json');
        $fixture = [
            'users' => [
                [
                    'id' => 'admin_test',
                    'student_id' => null,
                    'email' => 'admin.test@example.com',
                    'password' => 'secret123',
                    'role' => 'admin',
                    'first_name' => 'Test',
                    'middle_name' => '',
                    'last_name' => 'Admin',
                    'year_level' => null,
                    'course' => null,
                    'contact' => null,
                    'sport' => 'none',
                    'avatar' => null,
                ],
            ],
            'attendance' => [
                '2024001' => [
                    '2026-08-08' => ['status' => 'present', 'time' => '08:00', 'note' => 'Migrated', 'sport' => 'taekwondo'],
                ],
            ],
            'excuses' => [],
            'schedules' => [
                'taekwondo' => [
                    '2026-08-10' => ['time' => '8:00 AM - 10:00 AM', 'venue' => 'Main Gym', 'created_at' => '2026-08-01 00:00:00'],
                ],
            ],
            'announcements' => [],
            'schedule_updates' => ['taekwondo' => '2026-08-01 00:00:00'],
            'schedule_draft_updates' => ['taekwondo' => '2026-08-01 00:00:00'],
            'notification_states' => [],
        ];

        file_put_contents($jsonPath, json_encode($fixture, JSON_PRETTY_PRINT));

        AttendanceStore::migrateLegacyJsonToDatabase();

        $this->assertDatabaseHas('attendance_users', ['email' => 'admin.test@example.com']);
        $this->assertDatabaseHas('attendance_records', ['student_id' => '2024001', 'date' => '2026-08-08']);
        $this->assertDatabaseHas('attendance_schedules', ['sport' => 'taekwondo', 'date' => '2026-08-10']);
    }

    public function testAutoMarksAllSundaysAsNoTrainingInMonthlyCalendar()
    {
        $data = [
            'users' => [
                [
                    'id' => 'student_sunday_check',
                    'student_id' => '2024010',
                    'email' => 'student.sunday@example.com',
                    'password' => 'secret123',
                    'role' => 'student',
                    'first_name' => 'Sunday',
                    'middle_name' => '',
                    'last_name' => 'Student',
                    'year_level' => '2',
                    'course' => 'BSIT',
                    'contact' => '09123456789',
                    'sport' => 'taekwondo',
                    'avatar' => null,
                ],
            ],
            'attendance' => [],
            'excuses' => [],
            'schedules' => [],
            'announcements' => [],
            'special_training_requests' => [],
        ];

        AttendanceStore::save($data, false);

        // August 2026 has Sundays on: 2, 9, 16, 23, 30
        $calendar = AttendanceStore::getMonthlyStatusCalendar('2024010', 2026, 8);

        $this->assertSame('no_training', $calendar['2026-08-02']['status']);
        $this->assertSame('no_training', $calendar['2026-08-09']['status']);
        $this->assertSame('no_training', $calendar['2026-08-16']['status']);
        $this->assertSame('no_training', $calendar['2026-08-23']['status']);
        $this->assertSame('no_training', $calendar['2026-08-30']['status']);
    }

    public function testPendingExcusesOlderThanOneWeekAreRemoved()
    {
        $oldDate = now()->subDays(8)->format('Y-m-d H:i:s');
        $data = [
            'users' => [],
            'attendance' => [],
            'excuses' => [
                [
                    'id' => 'exc_old',
                    'student_id' => '2024005',
                    'date' => '2026-08-01',
                    'title' => 'Old excuse',
                    'reason' => 'Outdated request',
                    'attachments' => [],
                    'sport' => 'taekwondo',
                    'approved' => false,
                    'submitted_at' => $oldDate,
                ],
                [
                    'id' => 'exc_recent',
                    'student_id' => '2024006',
                    'date' => '2026-08-08',
                    'title' => 'Recent excuse',
                    'reason' => 'Still valid',
                    'attachments' => [],
                    'sport' => 'taekwondo',
                    'approved' => false,
                    'submitted_at' => now()->subDays(3)->format('Y-m-d H:i:s'),
                ],
            ],
            'schedules' => [],
            'announcements' => [],
            'special_training_requests' => [],
        ];

        AttendanceStore::save($data, false);

        $excuses = AttendanceStore::getExcuses('taekwondo');
        $this->assertCount(1, $excuses);
        $this->assertSame('exc_recent', $excuses[0]['id']);
    }

    public function testPendingSpecialTrainingRequestsOlderThanOneWeekAreRemoved()
    {
        $oldDate = now()->subDays(8)->format('Y-m-d H:i:s');
        $data = [
            'users' => [],
            'attendance' => [],
            'excuses' => [],
            'schedules' => [],
            'announcements' => [],
            'special_training_requests' => [
                [
                    'id' => 'st_old',
                    'student_id' => '2024007',
                    'sport' => 'karatedo',
                    'start_date' => '2026-08-01',
                    'end_date' => '2026-08-05',
                    'title' => 'Old training',
                    'reason' => 'Expired request',
                    'attachments' => [],
                    'approved' => false,
                    'submitted_at' => $oldDate,
                ],
                [
                    'id' => 'st_recent',
                    'student_id' => '2024008',
                    'sport' => 'karatedo',
                    'start_date' => '2026-08-10',
                    'end_date' => '2026-08-12',
                    'title' => 'Recent training',
                    'reason' => 'Still valid',
                    'attachments' => [],
                    'approved' => false,
                    'submitted_at' => now()->subDays(2)->format('Y-m-d H:i:s'),
                ],
            ],
        ];

        AttendanceStore::save($data, false);

        $requests = AttendanceStore::getPendingSpecialTrainingRequests('karatedo');
        $this->assertCount(1, $requests);
        $this->assertSame('st_recent', $requests[0]['id']);
    }

    public function testAnnouncementsOlderThanOneWeekAreRemoved()
    {
        $oldDate = now()->subDays(8)->format('Y-m-d H:i:s');
        $data = [
            'users' => [],
            'attendance' => [],
            'excuses' => [],
            'schedules' => [],
            'announcements' => [
                [
                    'id' => 'ann_old',
                    'sport' => 'all',
                    'title' => 'Old announcement',
                    'body' => 'This should expire',
                    'created_at' => $oldDate,
                    'updated_at' => $oldDate,
                ],
                [
                    'id' => 'ann_recent',
                    'sport' => 'all',
                    'title' => 'Current announcement',
                    'body' => 'This should remain',
                    'created_at' => now()->subDays(2)->format('Y-m-d H:i:s'),
                    'updated_at' => now()->subDays(2)->format('Y-m-d H:i:s'),
                ],
            ],
            'special_training_requests' => [],
        ];

        AttendanceStore::save($data, false);

        $announcements = AttendanceStore::getAnnouncements('all');
        $this->assertCount(1, $announcements);
        $this->assertSame('ann_recent', $announcements[0]['id']);
    }
}

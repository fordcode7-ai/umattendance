<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    protected function importLegacyJson(): void
    {
        $path = storage_path('app/attendance_data.json');
        if (!file_exists($path)) {
            return;
        }

        $raw = file_get_contents($path);
        if ($raw === false || trim($raw) === '') {
            return;
        }

        $data = json_decode($raw, true);
        if (!is_array($data)) {
            return;
        }

        foreach ($data['users'] ?? [] as $user) {
            DB::table('attendance_users')->updateOrInsert(
                ['id' => $user['id']],
                [
                    'student_id' => $user['student_id'] ?? null,
                    'email' => $user['email'] ?? null,
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
                    'sports_handled' => isset($user['sports_handled']) ? json_encode($user['sports_handled']) : json_encode([]),
                    'created_at' => $user['created_at'] ?? now(),
                    'updated_at' => $user['updated_at'] ?? now(),
                ]
            );
        }

        foreach ($data['attendance'] ?? [] as $studentId => $records) {
            foreach ($records as $date => $entry) {
                DB::table('attendance_records')->updateOrInsert(
                    ['student_id' => $studentId, 'date' => $date],
                    [
                        'status' => $entry['status'] ?? 'absent',
                        'time' => $entry['time'] ?? null,
                        'note' => $entry['note'] ?? '',
                        'sport' => $entry['sport'] ?? 'taekwondo',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        foreach ($data['excuses'] ?? [] as $excuse) {
            DB::table('attendance_excuses')->updateOrInsert(
                ['id' => $excuse['id']],
                [
                    'student_id' => $excuse['student_id'],
                    'date' => $excuse['date'],
                    'reason' => $excuse['reason'],
                    'attachments' => json_encode($excuse['attachments'] ?? []),
                    'sport' => $excuse['sport'],
                    'approved' => !empty($excuse['approved']) ? 1 : 0,
                    'submitted_at' => $excuse['submitted_at'] ?? now(),
                    'approved_at' => $excuse['approved_at'] ?? null,
                    'created_at' => $excuse['created_at'] ?? now(),
                    'updated_at' => $excuse['updated_at'] ?? now(),
                ]
            );
        }

        foreach ($data['schedules'] ?? [] as $sport => $entries) {
            foreach ($entries as $date => $entry) {
                DB::table('attendance_schedules')->updateOrInsert(
                    ['sport' => $sport, 'date' => $date],
                    [
                        'time' => $entry['time'] ?? null,
                        'venue' => $entry['venue'] ?? null,
                        'created_at' => $entry['created_at'] ?? now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        foreach ($data['announcements'] ?? [] as $announcement) {
            DB::table('attendance_announcements')->updateOrInsert(
                ['id' => $announcement['id']],
                [
                    'sport' => $announcement['sport'],
                    'title' => $announcement['title'],
                    'body' => $announcement['body'],
                    'created_at' => $announcement['created_at'] ?? now(),
                    'updated_at' => $announcement['updated_at'] ?? now(),
                ]
            );
        }

        foreach (['schedule_updates', 'schedule_draft_updates', 'notification_states'] as $key) {
            if (!array_key_exists($key, $data)) {
                continue;
            }
            DB::table('attendance_system_settings')->updateOrInsert(
                ['setting_key' => $key],
                [
                    'setting_value' => json_encode($data[$key]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (DB::table('attendance_users')->count() === 0) {
            $this->importLegacyJson();
        }

        if (DB::table('attendance_users')->count() > 0) {
            return;
        }

        DB::table('attendance_users')->insert([
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
                'title' => null,
                'sports_handled' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
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
                'title' => null,
                'sports_handled' => json_encode(['taekwondo']),
                'created_at' => now(),
                'updated_at' => now(),
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
                'title' => null,
                'sports_handled' => json_encode(['karatedo']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}

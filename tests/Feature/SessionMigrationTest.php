<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class SessionMigrationTest extends TestCase
{
    public function test_the_application_defines_a_sessions_table_migration(): void
    {
        $migrations = File::files(database_path('migrations'));

        $hasSessionMigration = collect($migrations)->contains(function ($file) {
            return str_contains($file->getFilename(), 'create_sessions_table');
        });

        $this->assertTrue($hasSessionMigration, 'The sessions table migration is missing.');
    }
}

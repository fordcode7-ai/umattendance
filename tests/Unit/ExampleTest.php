<?php

namespace Tests\Unit;

use App\Services\AttendanceStore;
use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    public function test_recent_timestamp_is_marked_as_new(): void
    {
        $this->assertTrue(AttendanceStore::isNewItem(now()->subHours(2)->toDateTimeString()));
    }

    public function test_old_timestamp_is_not_marked_as_new(): void
    {
        $this->assertFalse(AttendanceStore::isNewItem(now()->subDays(2)->toDateTimeString()));
    }
}

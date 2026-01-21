<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::create(2025, 1, 15, 9, 0, 0));
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Carbon::setTestNow();
    }

    /**
     * 勤怠詳細画面の「名前」がログインユーザーの氏名になっている
     */
    public function test_勤怠詳細画面の名前がログインユーザーの氏名になっている()
    {
        $user = User::factory()->create(['name' => '山田太郎']);

        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => Carbon::create(2025, 1, 15),
            'clock_in'  => Carbon::create(2025, 1, 15, 9, 0),
            'clock_out' => Carbon::create(2025, 1, 15, 18, 0),
            'status'    => Attendance::STATUS_CLOCKED_OUT,
        ]);

        $response = $this
            ->actingAs($user)
            ->get("/attendance/detail/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee('山田太郎');
    }

    /**
     * 勤怠詳細画面の「日付」が選択した日付になっている
     */
    public function test_勤怠詳細画面の日付が選択した日付になっている()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => Carbon::create(2025, 1, 15),
            'clock_in'  => Carbon::create(2025, 1, 15, 9, 0),
            'clock_out' => Carbon::create(2025, 1, 15, 18, 0),
            'status'    => Attendance::STATUS_CLOCKED_OUT,
        ]);

        $response = $this
            ->actingAs($user)
            ->get("/attendance/detail/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee('2025');
        $response->assertSee('01月15日');
    }

    /**
     * 「出勤・退勤」にて記されている時間がログインユーザーの打刻と一致している
     */
    public function test_出勤退勤時間が打刻と一致している()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => Carbon::create(2025, 1, 15),
            'clock_in'  => Carbon::create(2025, 1, 15, 9, 0),
            'clock_out' => Carbon::create(2025, 1, 15, 18, 0),
            'status'    => Attendance::STATUS_CLOCKED_OUT,
        ]);

        $response = $this
            ->actingAs($user)
            ->get("/attendance/detail/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /**
     * 「休憩」にて記されている時間がログインユーザーの打刻と一致している
     */
    public function test_休憩時間が打刻と一致している()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => Carbon::create(2025, 1, 15),
            'clock_in'  => Carbon::create(2025, 1, 15, 9, 0),
            'clock_out' => Carbon::create(2025, 1, 15, 18, 0),
            'status'    => Attendance::STATUS_CLOCKED_OUT,
        ]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start'   => Carbon::create(2025, 1, 15, 12, 0),
            'break_end'     => Carbon::create(2025, 1, 15, 13, 0),
        ]);

        $response = $this
            ->actingAs($user)
            ->get("/attendance/detail/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }
}
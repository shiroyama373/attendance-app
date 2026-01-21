<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AttendanceStatusTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // 時刻を固定（テストの安定性確保）
        Carbon::setTestNow(Carbon::create(2025, 1, 15, 10, 0, 0));
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Carbon::setTestNow();
    }

    /**
     * 勤務外の場合、勤怠ステータスが正しく表示される
     */
    public function test_勤務外の場合勤怠ステータスが正しく表示される()
    {
        $user = User::create([
            'name' => '山田太郎',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'is_admin' => false,
            'email_verified_at' => now(),
        ]);

        $response = $this
        ->actingAs($user)
        ->withoutMiddleware()
        ->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('勤務外');
    }

    /**
     * 出勤中の場合、勤怠ステータスが正しく表示される
     */
    public function test_出勤中の場合勤怠ステータスが正しく表示される()
    {
        $user = User::create([
            'name' => '山田太郎',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'is_admin' => false,
            'email_verified_at' => now(),
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'clock_in' => Carbon::now(),
            'status' => 'clocked_in',
        ]);

        $response = $this
        ->actingAs($user)
        ->withoutMiddleware()
        ->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('出勤中');
    }

    /**
     * 休憩中の場合、勤怠ステータスが正しく表示される
     */
    public function test_休憩中の場合勤怠ステータスが正しく表示される()
    {
        $user = User::create([
            'name' => '山田太郎',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'is_admin' => false,
            'email_verified_at' => now(),
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'clock_in' => Carbon::now(),
            'status' => 'on_break',
        ]);

        $response = $this
        ->actingAs($user)
        ->withoutMiddleware()
        ->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('休憩中');
    }

    /**
     * 退勤済の場合、勤怠ステータスが正しく表示される
     */
    public function test_退勤済の場合勤怠ステータスが正しく表示される()
    {
        $user = User::create([
            'name' => '山田太郎',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'is_admin' => false,
            'email_verified_at' => now(),
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'clock_in' => Carbon::now()->subHours(8),
            'clock_out' => Carbon::now(),
            'status' => 'clocked_out',
        ]);

        $response = $this
        ->actingAs($user)
        ->withoutMiddleware()
        ->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('退勤済');
    }
}
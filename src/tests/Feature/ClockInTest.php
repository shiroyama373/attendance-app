<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class ClockInTest extends TestCase
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
     * 出勤ボタンが正しく機能する
     */
    public function test_出勤ボタンが正しく機能する()
    {
        $user = User::create([
            'name' => '山田太郎',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'is_admin' => false,
            'email_verified_at' => now(),
        ]);

        // 勤怠画面を表示
        $response = $this
            ->actingAs($user)
            ->withoutMiddleware()  
            ->get('/attendance');
        
        $response->assertStatus(200);
        $response->assertSee('出勤');

        // 出勤処理
        $response = $this
            ->actingAs($user)
            ->withoutMiddleware()
            ->post('/attendance', [
                'action' => 'clock_in',
            ]);

        $response->assertRedirect('/attendance');

        // 出勤後のステータス確認
        $response = $this
            ->actingAs($user)
            ->withoutMiddleware()  
            ->get('/attendance');
        
        $response->assertSee('出勤中');
    }

    /**
     * 出勤は一日一回のみできる
     */
    public function test_出勤は一日一回のみできる()
    {
        $user = User::create([
            'name' => '山田太郎',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'is_admin' => false,
            'email_verified_at' => now(),
        ]);

        // 既に退勤済みの勤怠データを作成
        Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'clock_in' => Carbon::now()->subHours(8),
            'clock_out' => Carbon::now()->subHours(1),
            'status' => 'clocked_out',
        ]);

        // 勤怠画面を表示
        $response = $this
            ->actingAs($user)
            ->withoutMiddleware()  
            ->get('/attendance');

        $response->assertStatus(200);
        // 退勤済みの状態が表示されていることを確認
        $response->assertSee('退勤済');
        $response->assertSee('お疲れ様でした');
        // 出勤ボタンが存在しないことを確認（より具体的に）
        $response->assertDontSee('<button', false);  // ボタン自体が存在しない

    }

    /**
     * 出勤時刻が勤怠一覧画面で確認できる
     */
    public function test_出勤時刻が勤怠一覧画面で確認できる()
    {
        $user = User::create([
            'name' => '山田太郎',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'is_admin' => false,
            'email_verified_at' => now(),
        ]);

        // 出勤処理
        $response = $this
            ->actingAs($user)
            ->withoutMiddleware()
            ->post('/attendance', [
                'action' => 'clock_in',
            ]);

        // 勤怠一覧画面を表示
        $response = $this
            ->actingAs($user)
            ->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('09:00');
    }
}
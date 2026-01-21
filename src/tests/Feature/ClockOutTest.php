<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class ClockOutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::create(2025, 1, 15, 18, 0, 0));
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Carbon::setTestNow();
    }

    /**
     * 出勤中の勤怠データを作成するヘルパーメソッド
     */
    private function createWorkingAttendance(User $user)
    {
        return Attendance::create([
            'user_id'   => $user->id,
            'work_date' => Carbon::today(),
            'clock_in'  => Carbon::now()->subHours(8),
            'status'    => Attendance::STATUS_CLOCKED_IN,
        ]);
    }

    /**
     * 退勤ボタンが正しく機能する
     */
    public function test_退勤ボタンが正しく機能する()
    {
        $user = User::factory()->create();
        $this->createWorkingAttendance($user);
        $this->actingAs($user);

        // 退勤処理
        $response = $this->post('/attendance', [
            'action' => 'clock_out',
        ]);

        $response->assertRedirect('/attendance');

        // 退勤済のステータス＋退勤時刻を確認
        $this->assertDatabaseHas('attendances', [
            'user_id'   => $user->id,
            'status'    => Attendance::STATUS_CLOCKED_OUT,
            'clock_out' => Carbon::now(),
        ]);

        // 画面で「退勤済」が表示される
        $response = $this->get('/attendance');
        $response->assertSee('退勤済');
    }

    /**
     * 退勤時刻が勤怠一覧画面で確認できる
     */
    public function test_退勤時刻が勤怠一覧画面で確認できる()
    {
        $user = User::factory()->create();
        $this->createWorkingAttendance($user);
        $this->actingAs($user);

        // 退勤処理
        $this->post('/attendance', [
            'action' => 'clock_out',
        ]);

        // 勤怠一覧画面を表示
        $response = $this->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('18:00');
    }
}
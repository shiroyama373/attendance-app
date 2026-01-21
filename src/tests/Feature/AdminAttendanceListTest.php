<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminAttendanceListTest extends TestCase
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
     * 勤怠データ作成用ヘルパー
     */
    private function createAttendance(User $user, Carbon $date)
    {
        return Attendance::create([
            'user_id'   => $user->id,
            'work_date' => $date,
            'clock_in'  => $date->copy()->setTime(9, 0),
            'clock_out' => $date->copy()->setTime(18, 0),
            'status'    => Attendance::STATUS_CLOCKED_OUT,
        ]);
    }

    /**
     * その日になされた全ユーザーの勤怠情報が正確に確認できる
     */
    public function test_その日になされた全ユーザーの勤怠情報が正確に確認できる()
    {
        // 管理者ユーザー作成
        $admin = User::factory()->create(['is_admin' => true]);

        // 一般ユーザー2人作成
        $user1 = User::factory()->create(['name' => '山田太郎']);
        $user2 = User::factory()->create(['name' => '佐藤花子']);

        // 今日の勤怠データを作成
        $this->createAttendance($user1, Carbon::today());
        $this->createAttendance($user2, Carbon::today());

        // 管理者として勤怠一覧画面を表示
        $response = $this
            ->actingAs($admin)
            ->withoutMiddleware()
            ->get('/admin/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('山田太郎');
        $response->assertSee('佐藤花子');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /**
     * 遷移した際に現在の日付が表示される
     */
    public function test_遷移した際に現在の日付が表示される()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this
            ->actingAs($admin)
            ->withoutMiddleware()
            ->get('/admin/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('2025年01月15日');
    }

    /**
     * 「前日」を押下した時に前の日の勤怠情報が表示される
     */
    public function test_前日を押下した時に前の日の勤怠情報が表示される()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['name' => '山田太郎']);

        // 前日の勤怠データを作成
        $this->createAttendance($user, Carbon::create(2025, 1, 14));

        $response = $this
            ->actingAs($admin)
            ->withoutMiddleware()
            ->get('/admin/attendance/list?date=2025-01-14');

        $response->assertStatus(200);
        $response->assertSee('2025年01月14日');
        $response->assertSee('山田太郎');
    }

    /**
     * 「翌日」を押下した時に次の日の勤怠情報が表示される
     */
    public function test_翌日を押下した時に次の日の勤怠情報が表示される()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['name' => '山田太郎']);

        // 翌日の勤怠データを作成
        $this->createAttendance($user, Carbon::create(2025, 1, 16));

        $response = $this
            ->actingAs($admin)
            ->withoutMiddleware()
            ->get('/admin/attendance/list?date=2025-01-16');

        $response->assertStatus(200);
        $response->assertSee('2025年01月16日');
        $response->assertSee('山田太郎');
    }
}
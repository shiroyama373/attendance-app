<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminStaffTest extends TestCase
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
     * 管理者ユーザーが全一般ユーザーの「氏名」「メールアドレス」を確認できる
     */
    public function test_管理者ユーザーが全一般ユーザーの氏名メールアドレスを確認できる()
    {
        // 管理者ユーザー作成
        $admin = User::factory()->create(['is_admin' => true]);

        // 一般ユーザー作成
        $user1 = User::factory()->create([
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
        ]);
        $user2 = User::factory()->create([
            'name' => '佐藤花子',
            'email' => 'sato@example.com',
        ]);

        // スタッフ一覧画面を表示
        $response = $this
            ->actingAs($admin)
            ->get('/admin/staff/list');

        $response->assertStatus(200);
        $response->assertSee('山田太郎');
        $response->assertSee('yamada@example.com');
        $response->assertSee('佐藤花子');
        $response->assertSee('sato@example.com');
    }

    /**
     * ユーザーの勤怠情報が正しく表示される
     */
    public function test_ユーザーの勤怠情報が正しく表示される()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['name' => '山田太郎']);

        // 勤怠データ作成
        $this->createAttendance($user, Carbon::today());

        // ユーザーの勤怠一覧画面を表示
        $response = $this
            ->actingAs($admin)
            ->get("/admin/attendance/staff/{$user->id}");

        $response->assertStatus(200);
        $response->assertSee('山田太郎');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /**
     * 「前月」を押下した時に表示月の前月の情報が表示される
     */
    public function test_前月を押下した時に表示月の前月の情報が表示される()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['name' => '山田太郎']);

        // 前月の勤怠データを作成
        $this->createAttendance($user, Carbon::create(2024, 12, 15));

        $response = $this
            ->actingAs($admin)
            ->get("/admin/attendance/staff/{$user->id}?year=2024&month=12");

        $response->assertStatus(200);
        $response->assertSee('2024年12月');
        $response->assertSee('山田太郎');
    }

    /**
     * 「翌月」を押下した時に表示月の翌月の情報が表示される
     */
    public function test_翌月を押下した時に表示月の翌月の情報が表示される()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['name' => '山田太郎']);

        // 翌月の勤怠データを作成
        $this->createAttendance($user, Carbon::create(2025, 2, 15));

        $response = $this
            ->actingAs($admin)
            ->get("/admin/attendance/staff/{$user->id}?year=2025&month=2");

        $response->assertStatus(200);
        $response->assertSee('2025年2月');
        $response->assertSee('山田太郎');
    }

    /**
     * 「詳細」を押下すると、その日の勤怠詳細画面に遷移する
     */
    public function test_詳細を押下するとその日の勤怠詳細画面に遷移する()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['name' => '山田太郎']);

        // 勤怠データを作成
        $attendance = $this->createAttendance($user, Carbon::today());

        // 詳細画面に遷移
        $response = $this
            ->actingAs($admin)
            ->get("/admin/attendance/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee('山田太郎');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }
}
<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceListTest extends TestCase
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
     * 自分が行った勤怠情報が全て表示されている
     */
    public function test_自分が行った勤怠情報が全て表示されている()
    {
        $user = User::factory()->create();

        $this->createAttendance($user, Carbon::create(2025, 1, 10));
        $this->createAttendance($user, Carbon::create(2025, 1, 13));

        $response = $this
            ->actingAs($user)
            ->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('01/10');
        $response->assertSee('01/13');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /**
     * 勤怠一覧画面に遷移した際に現在の月が表示される
     */
    public function test_勤怠一覧画面に遷移した際に現在の月が表示される()
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('2025年1月');
    }

    /**
     * 「前月」を押下した時に表示月の前月の情報が表示される
     */
    public function test_前月を押下した時に表示月の前月の情報が表示される()
    {
        $user = User::factory()->create();

        $this->createAttendance($user, Carbon::create(2024, 12, 15));

        $response = $this
            ->actingAs($user)
            ->get('/attendance/list?year=2024&month=12');

        $response->assertStatus(200);
        $response->assertSee('2024年12月');
        $response->assertSee('12/15');
    }

    /**
     * 「翌月」を押下した時に表示月の翌月の情報が表示される
     */
    public function test_翌月を押下した時に表示月の翌月の情報が表示される()
    {
        $user = User::factory()->create();

        $this->createAttendance($user, Carbon::create(2025, 2, 15));

        $response = $this
            ->actingAs($user)
            ->get('/attendance/list?year=2025&month=2');

        $response->assertStatus(200);
        $response->assertSee('2025年2月');
        $response->assertSee('02/15');
    }

    /**
     * 「詳細」を押下すると、その日の勤怠詳細画面に遷移する
     */
    public function test_詳細を押下するとその日の勤怠詳細画面に遷移する()
    {
        $user = User::factory()->create();

        $attendance = $this->createAttendance(
            $user,
            Carbon::create(2025, 1, 15)
        );

        $response = $this
            ->actingAs($user)
            ->get("/attendance/detail/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee('2025');
        $response->assertSee('01月15日');
    }
}
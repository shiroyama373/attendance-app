<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class AdminAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $user;
    private Attendance $attendance;

    protected function setUp(): void
    {
        parent::setUp();

        // 管理者ユーザー作成
        $this->admin = User::factory()->create(['is_admin' => true]);

        // 一般ユーザー作成
        $this->user = User::factory()->create(['name' => '山田太郎']);

        // 出勤レコード作成
        $this->attendance = Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => Carbon::today(),
            'clock_in' => Carbon::today()->setTime(9, 0),
            'clock_out' => Carbon::today()->setTime(18, 0),
            'status' => Attendance::STATUS_CLOCKED_OUT,
        ]);

        // 休憩レコード作成
        BreakTime::create([
            'attendance_id' => $this->attendance->id,
            'break_start' => Carbon::today()->setTime(12, 0),
            'break_end' => Carbon::today()->setTime(13, 0),
        ]);
    }

    /**
     * 勤怠詳細画面に表示されるデータが選択したものになっている
     */
    public function test_勤怠詳細画面に表示されるデータが選択したものになっている()
    {
        $response = $this
            ->actingAs($this->admin)
            ->get("/admin/attendance/{$this->attendance->id}");

        $response->assertStatus(200);
        $response->assertSee('山田太郎');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }

    /**
     * 出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される
     */
    public function test_出勤時間が退勤時間より後になっている場合エラーメッセージが表示される()
    {
        $response = $this
            ->actingAs($this->admin)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->put(
                "/admin/attendance/{$this->attendance->id}",
                [
                    'clock_in' => '19:00',
                    'clock_out' => '18:00',
                    'note' => 'テスト備考',
                ]
            );

        $response->assertSessionHasErrors('clock_out');
    }

    /**
     * 休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される
     */
    public function test_休憩開始時間が退勤時間より後になっている場合エラーメッセージが表示される()
    {
        $response = $this
            ->actingAs($this->admin)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->put(
                "/admin/attendance/{$this->attendance->id}",
                [
                    'clock_in' => '09:00',
                    'clock_out' => '18:00',
                    'breaks_data' => [
                        [
                            'break_start' => '19:00',
                            'break_end' => '19:30',
                        ],
                    ],
                    'note' => 'テスト備考',
                ]
            );

        $response->assertSessionHasErrors('breaks_data.0.break_start');
    }

    /**
     * 休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される
     */
    public function test_休憩終了時間が退勤時間より後になっている場合エラーメッセージが表示される()
    {
        $response = $this
            ->actingAs($this->admin)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->put(
                "/admin/attendance/{$this->attendance->id}",
                [
                    'clock_in' => '09:00',
                    'clock_out' => '18:00',
                    'breaks_data' => [
                        [
                            'break_start' => '17:00',
                            'break_end' => '19:00',
                        ],
                    ],
                    'note' => 'テスト備考',
                ]
            );

        $response->assertSessionHasErrors('breaks_data.0.break_end');
    }

    /**
     * 備考欄が未入力の場合のエラーメッセージが表示される
     */
    public function test_備考欄が未入力の場合のエラーメッセージが表示される()
    {
        $response = $this
            ->actingAs($this->admin)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->put(
                "/admin/attendance/{$this->attendance->id}",
                [
                    'clock_in' => '09:00',
                    'clock_out' => '18:00',
                    'note' => '',
                ]
            );

        $response->assertSessionHasErrors('note');
    }
}
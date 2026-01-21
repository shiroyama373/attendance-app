<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class AttendanceUpdateValidationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Attendance $attendance;

    protected function setUp(): void
    {
        parent::setUp();

        // テスト用ユーザー作成
        $this->user = User::factory()->create();

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
     * 出勤時間が退勤時間より後の場合、エラーになる
     */
    public function test_出勤時間が退勤時間より後の場合エラーになる()
    {
        $response = $this
            ->actingAs($this->user)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post(
                "/attendance/detail/{$this->attendance->id}",
                [
                    'attendance_id' => $this->attendance->id,
                    'clock_in' => '19:00',
                    'clock_out' => '18:00',
                    'note' => 'テスト備考',
                ]
            );

        $response->assertSessionHasErrors('clock_out');
    }

    /**
     * 休憩開始時間が退勤時間より後の場合、エラーになる
     */
    public function test_休憩開始時間が退勤時間より後の場合エラーになる()
    {
        $response = $this
            ->actingAs($this->user)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post(
                "/attendance/detail/{$this->attendance->id}",
                [
                    'attendance_id' => $this->attendance->id,
                    'clock_in' => '09:00',
                    'clock_out' => '18:00',
                    'breaks_data' => [  // breaks → breaks_data に変更
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
     * 休憩終了時間が退勤時間より後の場合、エラーになる
     */
    public function test_休憩終了時間が退勤時間より後の場合エラーになる()
    {
        $response = $this
            ->actingAs($this->user)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post(
                "/attendance/detail/{$this->attendance->id}",
                [
                    'attendance_id' => $this->attendance->id,
                    'clock_in' => '09:00',
                    'clock_out' => '18:00',
                    'breaks_data' => [  // breaks → breaks_data に変更
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
     * 備考が未入力の場合、エラーになる
     */
    public function test_備考が未入力の場合エラーになる()
    {
        $response = $this
            ->actingAs($this->user)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post(
                "/attendance/detail/{$this->attendance->id}",
                [
                    'attendance_id' => $this->attendance->id,
                    'clock_in' => '09:00',
                    'clock_out' => '18:00',
                    'note' => '',
                ]
            );

        $response->assertSessionHasErrors('note');
    }

    /**
     * 正常な修正の場合、修正申請が作成される
     */
    public function test_修正申請処理が実行される()
    {
        $response = $this
            ->actingAs($this->user)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post(
                "/attendance/detail/{$this->attendance->id}",
                [
                    'attendance_id' => $this->attendance->id,
                    'clock_in' => '10:00',
                    'clock_out' => '19:00',
                    'breaks_data' => [  // breaks → breaks_data に変更
                        [
                            'break_start' => '13:00',
                            'break_end' => '14:00',
                        ],
                    ],
                    'note' => '修正申請テスト',
                ]
            );

        $this->assertDatabaseHas('stamp_correction_requests', [
            'attendance_id' => $this->attendance->id,
            'user_id' => $this->user->id,
            'status' => 'pending',
        ]);

        $response->assertRedirect();
    }
}
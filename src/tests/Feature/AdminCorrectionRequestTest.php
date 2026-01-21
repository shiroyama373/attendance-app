<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\StampCorrectionRequest;
use Carbon\Carbon;

class AdminCorrectionRequestTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 承認待ちの修正申請が全て表示されている
     */
    public function test_承認待ちの修正申請が全て表示されている()
    {
        // 管理者ユーザー作成
        $admin = User::factory()->create(['is_admin' => true]);

        // 一般ユーザー作成
        $user = User::factory()->create(['name' => '山田太郎']);

        // 勤怠データ作成
        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => Carbon::today(),
            'clock_in'  => Carbon::today()->setTime(9, 0),
            'clock_out' => Carbon::today()->setTime(18, 0),
            'status'    => Attendance::STATUS_CLOCKED_OUT,
        ]);

        // 承認待ちの修正申請を作成
        StampCorrectionRequest::create([
            'user_id'      => $user->id,
            'attendance_id'=> $attendance->id,
            'clock_in'     => Carbon::today()->setTime(10, 0),
            'clock_out'    => Carbon::today()->setTime(19, 0),
            'note'         => '遅刻しました',
            'status'       => 'pending',
        ]);

        // 修正申請一覧画面を表示
        $response = $this
            ->actingAs($admin)
            ->get('/stamp_correction_request/list');

        $response->assertStatus(200);
        $response->assertSee('山田太郎');
        $response->assertSee('遅刻しました');
    }

    /**
     * 承認済みの修正申請が全て表示されている
     */
    public function test_承認済みの修正申請が全て表示されている()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user  = User::factory()->create(['name' => '山田太郎']);

        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => Carbon::today(),
            'clock_in'  => Carbon::today()->setTime(9, 0),
            'clock_out' => Carbon::today()->setTime(18, 0),
            'status'    => Attendance::STATUS_CLOCKED_OUT,
        ]);

        StampCorrectionRequest::create([
            'user_id'       => $user->id,
            'attendance_id' => $attendance->id,
            'clock_in'      => Carbon::today()->setTime(10, 0),
            'clock_out'     => Carbon::today()->setTime(19, 0),
            'note'          => '遅刻しました',
            'status'        => 'approved',
        ]);

        $response = $this
            ->actingAs($admin)
            ->get('/stamp_correction_request/list?status=approved');

        $response->assertStatus(200);
        $response->assertSee('山田太郎');
        $response->assertSee('遅刻しました');
    }

    /**
     * 修正申請の詳細内容が正しく表示されている
     */
    public function test_修正申請の詳細内容が正しく表示されている()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user  = User::factory()->create(['name' => '山田太郎']);

        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => Carbon::today(),
            'clock_in'  => Carbon::today()->setTime(9, 0),
            'clock_out' => Carbon::today()->setTime(18, 0),
            'status'    => Attendance::STATUS_CLOCKED_OUT,
        ]);

        $request = StampCorrectionRequest::create([
            'user_id'       => $user->id,
            'attendance_id' => $attendance->id,
            'clock_in'      => Carbon::today()->setTime(10, 0),
            'clock_out'     => Carbon::today()->setTime(19, 0),
            'note'          => '遅刻しました',
            'status'        => 'pending',
        ]);

        $response = $this
            ->actingAs($admin)
            ->get("/stamp_correction_request/approve/{$request->id}");

        $response->assertStatus(200);
        $response->assertSee('山田太郎');
        $response->assertSee('10:00');
        $response->assertSee('19:00');
        $response->assertSee('遅刻しました');
    }

    /**
     * 修正申請の承認処理が正しく行われる
     */
    public function test_修正申請の承認処理が正しく行われる()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user  = User::factory()->create(['name' => '山田太郎']);

        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => Carbon::today(),
            'clock_in'  => Carbon::today()->setTime(9, 0),
            'clock_out' => Carbon::today()->setTime(18, 0),
            'status'    => Attendance::STATUS_CLOCKED_OUT,
        ]);

        $request = StampCorrectionRequest::create([
            'user_id'       => $user->id,
            'attendance_id' => $attendance->id,
            'clock_in'      => Carbon::today()->setTime(10, 0),
            'clock_out'     => Carbon::today()->setTime(19, 0),
            'note'          => '遅刻しました',
            'status'        => 'pending',
        ]);

        // 承認処理
        $response = $this
            ->actingAs($admin)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post("/stamp_correction_request/approve/{$request->id}", [
                'action' => 'approve',
            ]);

        $response->assertRedirect();

        // データベースで承認されたことを確認
        $this->assertDatabaseHas('stamp_correction_requests', [
            'id'     => $request->id,
            'status' => 'approved',
        ]);

        // 勤怠データが更新されたことを確認
        $this->assertDatabaseHas('attendances', [
            'id'       => $attendance->id,
            'clock_in' => Carbon::today()->setTime(10, 0),
            'clock_out'=> Carbon::today()->setTime(19, 0),
        ]);
    }
}
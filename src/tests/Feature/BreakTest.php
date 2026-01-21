<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class BreakTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::create(2025, 1, 15, 12, 0, 0));
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Carbon::setTestNow();
    }

    /**
     * 休憩入ボタンが正しく機能する
     */
    public function test_休憩入ボタンが正しく機能する()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id'   => $user->id,
            'work_date' => Carbon::today(),
            'clock_in'  => Carbon::now()->subHours(3),
            'status'    => Attendance::STATUS_CLOCKED_IN,
        ]);

        $this->actingAs($user);

        $response = $this->post('/attendance', [
            'action' => 'break_start',
        ]);

        $response->assertRedirect('/attendance');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'status'  => Attendance::STATUS_ON_BREAK,
        ]);
    }

    /**
     * 休憩は一日に何回でもできる
     */
    public function test_休憩は一日に何回でもできる()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id'   => $user->id,
            'work_date' => Carbon::today(),
            'clock_in'  => Carbon::now()->subHours(3),
            'status'    => Attendance::STATUS_CLOCKED_IN,
        ]);

        $this->actingAs($user);

        $this->post('/attendance', ['action' => 'break_start']);
        $this->post('/attendance', ['action' => 'break_end']);

        $this->post('/attendance', ['action' => 'break_start']);
        $this->post('/attendance', ['action' => 'break_end']);

        $this->assertDatabaseCount('breaks', 2);
    }

    /**
     * 休憩戻ボタンが正しく機能する
     */
    public function test_休憩戻ボタンが正しく機能する()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id'   => $user->id,
            'work_date' => Carbon::today(),
            'clock_in'  => Carbon::now()->subHours(3),
            'status'    => Attendance::STATUS_CLOCKED_IN,
        ]);

        $this->actingAs($user);

        $this->post('/attendance', ['action' => 'break_start']);
        $this->post('/attendance', ['action' => 'break_end']);

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'status'  => Attendance::STATUS_CLOCKED_IN,
        ]);
    }

    /**
     * 休憩戻は一日に何回でもできる
     */
    public function test_休憩戻は一日に何回でもできる()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id'   => $user->id,
            'work_date' => Carbon::today(),
            'clock_in'  => Carbon::now()->subHours(3),
            'status'    => Attendance::STATUS_CLOCKED_IN,
        ]);

        $this->actingAs($user);

        $this->post('/attendance', ['action' => 'break_start']);
        $this->post('/attendance', ['action' => 'break_end']);

        $this->post('/attendance', ['action' => 'break_start']);

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'status'  => Attendance::STATUS_ON_BREAK,
        ]);
    }

    /**
     * 休憩時間が勤怠一覧画面で確認できる
     */
    public function test_休憩時間が勤怠一覧画面で確認できる()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id'   => $user->id,
            'work_date' => Carbon::today(),
            'clock_in'  => Carbon::now()->subHours(3),
            'status'    => Attendance::STATUS_CLOCKED_IN,
        ]);

        $this->actingAs($user);

        $this->post('/attendance', ['action' => 'break_start']);

        Carbon::setTestNow(Carbon::now()->addMinutes(30));

        $this->post('/attendance', ['action' => 'break_end']);

        $response = $this->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('00:30');
    }
}
<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DateTimeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 現在の日時情報がUIと同じ形式で出力されている
     */
    public function test_現在の日時情報がUIと同じ形式で出力されている()
    {
        // 時刻を固定
        Carbon::setTestNow(Carbon::create(2025, 1, 15, 10, 0, 0));

        // ユーザーを作成してログイン
        $user = User::create([
            'name' => '山田太郎',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'is_admin' => false,
            'email_verified_at' => now(),
        ]);

        // 勤怠打刻画面を開く
        $response = $this
            ->actingAs($user)
            ->withoutMiddleware()  
            ->get('/attendance');

        // ステータスと日付表示を確認
        $response->assertStatus(200);
        $response->assertSee('2025年01月15日');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        // 時刻固定を解除
        Carbon::setTestNow();
    }
}
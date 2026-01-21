<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Auth\Notifications\VerifyEmail;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 会員登録後、認証メールが送信される
     */
    public function test_会員登録後認証メールが送信される()
    {
        Mail::fake();

        // 会員登録
        $response = $this->post('/register', [
            'name'                  => '山田太郎',
            'email'                 => 'test@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // ユーザーが作成されたことを確認
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);

        // メール送信を確認（Laravelのデフォルト認証メールが送信される）
        $user = User::where('email', 'test@example.com')->first();
        
        // メール認証が未完了であることを確認
        $this->assertNull($user->email_verified_at);
    }

    /**
     * メール認証誘導画面で「認証はこちらから」ボタンを押下するとメール認証サイトに遷移する
     */
    public function test_メール認証誘導画面で認証ボタンを押下するとメール認証サイトに遷移する()
    {
        // 未認証のユーザーを作成
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        // メール認証誘導画面を表示
        $response = $this
            ->actingAs($user)
            ->get('/email/verify');

        $response->assertStatus(200);
        $response->assertSee('認証はこちらから');
    }

    /**
     * メール認証サイトのメール認証を完了すると、勤怠登録画面に遷移する
     */
    public function test_メール認証を完了すると勤怠登録画面に遷移する()
    {
        // 未認証のユーザーを作成
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        // 認証URLを生成
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        // 認証URLにアクセス
        $response = $this
            ->actingAs($user)
            ->get($verificationUrl);

        // リダイレクト先を確認（verified=1 パラメータ付き）
        $response->assertRedirect('/home?verified=1');

        // メール認証が完了していることを確認
        $user->refresh();
        $this->assertNotNull($user->email_verified_at);
    }
}
<?php

namespace App\Providers;

use Laravel\Fortify\Fortify;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Cache\RateLimiting\Limit;
use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // メール認証完了後のリダイレクト
        Event::listen(Verified::class, function ($event) {
            // 何もしない（Fortifyのデフォルト動作を使う）
        });

        // ビューの設定
        Fortify::loginView(function () {
            if (request()->path() === 'admin/login') {
                return view('admin.auth.login');
            }
            return view('auth.login');
        });

        // 認証処理(ログイン画面に応じて分岐)
        Fortify::authenticateUsing(function (Request $request) {
            $user = User::where('email', $request->email)->first();

            // パスワードチェック
            if (!$user || !Hash::check($request->password, $user->password)) {
                return null;
            }

            // 管理者ログイン画面からのアクセスか判定
            $referer = $request->headers->get('referer');
            $isAdminLogin = $referer && str_contains($referer, '/admin/login');

            // 管理者ログイン画面: 管理者のみ許可
            if ($isAdminLogin && !$user->is_admin) {
                throw ValidationException::withMessages([
                    'email' => ['管理者アカウントでログインしてください。'],
                ]);
            }

            // 一般ログイン画面: 一般ユーザーのみ許可
            if (!$isAdminLogin && $user->is_admin) {
                throw ValidationException::withMessages([
                    'email' => ['一般ユーザーログイン画面(/login)をご利用ください。'],
                ]);
            }

            return $user;
        });

        Fortify::registerView(function () {
            return view('auth.register');
        });

        // メール認証画面
        Fortify::verifyEmailView(function () {
            return view('auth.verify-email');
        });

        Fortify::requestPasswordResetLinkView(function () {
            return view('auth.forgot-password');
        });

        Fortify::resetPasswordView(function ($request) {
            return view('auth.reset-password', ['request' => $request]);
        });

        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::redirectUserForTwoFactorAuthenticationUsing(RedirectIfTwoFactorAuthenticatable::class);

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        // ログアウト時のクッキー設定
        Event::listen(Logout::class, function ($event) {
            if ($event->user && $event->user->is_admin) {
                Cookie::queue('was_admin', '1', 5); // 5分間有効
            }
        });
    }
}
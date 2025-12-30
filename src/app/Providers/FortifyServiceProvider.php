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

        // 認証処理（シンプル版）
        Fortify::authenticateUsing(function (Request $request) {
            $credentials = $request->only('email', 'password');
            
            if (Auth::attempt($credentials)) {
                return Auth::user();
            }
            
            return null;
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
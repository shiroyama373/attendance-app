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
           // ビューの設定
    Fortify::loginView(function () {
        // URLで一般ユーザーと管理者を判定
        if (request()->is('admin/login')) {
            return view('admin.auth.login');
        }
        return view('auth.login');
    });

// 管理者ログインの認証処理
Fortify::authenticateUsing(function (Request $request) {
    $credentials = $request->only('email', 'password');
    
    if (Auth::attempt($credentials)) {
        $user = Auth::user();
        
        // 管理者ログインページからのログイン
        if ($request->path() === 'admin/login' && !$user->is_admin) {
            Auth::logout();
            return null;  // 一般ユーザーは管理者ログイン不可
        }
        
        // 一般ユーザーログインページからのログイン
        if ($request->path() !== 'admin/login' && $user->is_admin) {
            Auth::logout();
            return null;  // 管理者は一般ユーザーログイン不可
        }
        
        return $user;  // それ以外は成功
    }
    
    return null;
});


    Fortify::registerView(function () {
        return view('auth.register');
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

        RateLimiter::for('two-factor', function (Request $request) {
    return Limit::perMinute(5)->by($request->session()->get('login.id'));
});

// ログイン後のリダイレクト先を分岐
Fortify::redirects('login', function () {
    if (auth()->user()->is_admin) {
        return '/admin/attendance/list';
    }
    return '/attendance';
});

// ログアウトイベントをリッスン（クッキーにフラグを保存）
Event::listen(Logout::class, function ($event) {
    if ($event->user && $event->user->is_admin) {
        Cookie::queue('was_admin', '1', 5); // 5分間有効
    }
});

    }
}

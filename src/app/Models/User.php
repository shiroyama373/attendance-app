<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_admin' => 'boolean',
    ];

        /**
     * リレーション: ユーザーは複数の勤怠記録を持つ
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * リレーション: ユーザーは複数の修正申請を作成できる
     */
    public function stampCorrectionRequests()
    {
        return $this->hasMany(StampCorrectionRequest::class, 'user_id');
    }

    /**
     * リレーション: 管理者として複数の申請を承認できる
     */
    public function approvedRequests()
    {
        return $this->hasMany(StampCorrectionRequest::class, 'approved_by');
    }
}

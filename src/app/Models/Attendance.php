<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'work_date',
        'clock_in',
        'clock_out',
        'status',
        'note',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'work_date' => 'date',
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
    ];

    /**
     * リレーション: 勤怠記録は1人のユーザーに属する
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * リレーション: 1つの勤怠記録は複数の休憩を持つ
     */
    public function breaks()
    {
        return $this->hasMany(BreakTime::class);
    }

    /**
     * リレーション: 1つの勤怠記録に対して複数の修正申請が可能
     */
    public function stampCorrectionRequests()
    {
        return $this->hasMany(StampCorrectionRequest::class);
    }

    /**
     * 承認待ちの修正申請があるかチェック
     */
    public function hasPendingRequest()
    {
        return $this->stampCorrectionRequests()
            ->where('status', 'pending')
            ->exists();
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StampCorrectionRequest extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'attendance_id',
        'clock_in',
        'clock_out',
        'breaks_data',
        'note',
        'status',
        'approved_by',
        'approved_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
        'breaks_data' => 'array',
        'approved_at' => 'datetime',
    ];

    /**
     * リレーション: 申請は1人のユーザーによって作成される
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * リレーション: 申請は1つの勤怠記録に対して行われる
     */
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    /**
     * リレーション: 申請を承認した管理者
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * スコープ: 承認待ちの申請のみ取得
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * スコープ: 承認済みの申請のみ取得
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * スコープ: 却下された申請のみ取得
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
}
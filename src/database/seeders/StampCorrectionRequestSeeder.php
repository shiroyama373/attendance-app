<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StampCorrectionRequest;
use App\Models\User;
use App\Models\Attendance;

class StampCorrectionRequestSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'yamada@example.com')->first();
        $admin = User::where('email', 'admin@example.com')->first();
        
        // 最新の勤怠データを2件取得
        $attendances = Attendance::where('user_id', $user->id)
            ->orderBy('work_date', 'desc')
            ->limit(2)
            ->get();

        if ($attendances->count() >= 1) {
            // 申請中の修正申請
            StampCorrectionRequest::create([
                'user_id' => $user->id,
                'attendance_id' => $attendances[0]->id,
                'clock_in' => $attendances[0]->clock_in,
                'clock_out' => $attendances[0]->clock_out?->addHours(1),
                'breaks_data' => '[{"break_start": "12:00", "break_end": "13:00"}]',
                'note' => '残業のため退勤時刻を修正します。',
                'status' => 'pending',
            ]);
        }

        if ($attendances->count() >= 2) {
            // 承認済みの修正申請
            StampCorrectionRequest::create([
                'user_id' => $user->id,
                'attendance_id' => $attendances[1]->id,
                'clock_in' => $attendances[1]->clock_in,
                'clock_out' => $attendances[1]->clock_out,
                'breaks_data' => '[{"break_start": "12:00", "break_end": "13:00"}]',
                'note' => '打刻忘れのため修正しました。',
                'status' => 'approved',
                'approved_by' => $admin->id,
                'approved_at' => now(),
            ]);
        }
    }
}
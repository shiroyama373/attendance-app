<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 一般ユーザーを取得
        $users = User::where('is_admin', false)->get();

        // 今月の1日から今日まで勤怠データを作成
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now();

        foreach ($users as $user) {
            for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                // 土日はスキップ
                if ($date->isWeekend()) {
                    continue;
                }

                // 出勤時刻（9:00 ± ランダム）
                $clockIn = $date->copy()->setTime(9, rand(0, 15));
                
                // 退勤時刻（18:00 ± ランダム）
                $clockOut = $date->copy()->setTime(18, rand(0, 30));

                // 勤怠レコードを作成
                $attendance = Attendance::create([
                    'user_id' => $user->id,
                    'work_date' => $date->toDateString(),
                    'clock_in' => $clockIn,
                    'clock_out' => $clockOut,
                    'status' => 'clocked_out',
                    'note' => null,
                ]);

                // 休憩時間を追加（12:00-13:00）
                $attendance->breaks()->create([
                    'break_start' => '12:00:00',
                    'break_end' => '13:00:00',
                ]);

                // ランダムで午後の休憩も追加
                if (rand(0, 2) === 0) {
                    $attendance->breaks()->create([
                        'break_start' => '15:00:00',
                        'break_end' => '15:15:00',
                    ]);
                }
            }
        }
    }
}
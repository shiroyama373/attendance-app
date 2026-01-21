<?php

namespace Database\Factories;

use App\Models\Attendance;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition()
    {
        return [
            'user_id' => 1,
            'work_date' => Carbon::today(),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ];
    }
}
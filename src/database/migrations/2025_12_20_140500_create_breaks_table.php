<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('breaks', function (Blueprint $table) {
            $table->id();

            // 出勤レコードとのリレーション
            $table->foreignId('attendance_id')
                  ->constrained('attendances')
                  ->cascadeOnDelete();

            // 休憩開始・終了
            $table->time('break_start');
            $table->time('break_end')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('breaks');
    }
};
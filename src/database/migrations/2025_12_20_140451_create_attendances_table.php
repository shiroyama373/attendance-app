<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();

            // users テーブルとのリレーション
            $table->foreignId('user_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // 勤務日
            $table->date('work_date');

            // 出勤・退勤時刻
            $table->dateTime('clock_in')->nullable();
            $table->dateTime('clock_out')->nullable();

            // 勤怠ステータス
            $table->enum('status', [
                'not_started',
                'clocked_in',
                'on_break',
                'clocked_out',
            ])->default('not_started');

            // 備考
            $table->text('note')->nullable();

            $table->timestamps();

            // 同じユーザーが同じ日に2件登録できないようにする
            $table->unique(['user_id', 'work_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};